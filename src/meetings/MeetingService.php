<?php

namespace App\meetings;

use App\core\ApiException;
use App\core\Mapper;
use App\meetings\dtos\AddAttendee;
use App\meetings\dtos\AttendeeAdded;
use App\meetings\dtos\AttendanceConfirmed;
use App\meetings\dtos\ConfirmAttendance;
use App\meetings\dtos\CreateMeeting;
use App\meetings\dtos\MeetingResponse;
use App\meetings\dtos\MeetingUpdated;
use App\meetings\dtos\NewMeetingCreated;
use App\meetings\dtos\UpdateMeeting;
use App\meetings\dtos\MeetingAttendeeResponse;
use App\users\UserService;

class MeetingService {

    private MeetingRepository $meetingRepository;
    private MeetingAttendeeRepository $attendeeRepository;
    private UserService $userService;

    public function __construct() {
        $this->meetingRepository = new MeetingRepository();
        $this->attendeeRepository = new MeetingAttendeeRepository();
        $this->userService = new UserService();
    }

    public function createMeeting(CreateMeeting $createDto): NewMeetingCreated {
        $this->userService->findUserById($createDto->organizerId);

        $newMeeting = new MeetingEntity();
        $newMeeting->title = $createDto->title;
        $newMeeting->description = $createDto->description;
        $newMeeting->start_datetime = $createDto->startDatetime;
        $newMeeting->estimated_duration = $createDto->estimatedDuration;
        $newMeeting->type = $createDto->type;
        $newMeeting->organizer_id = $createDto->organizerId;

        $newMeetingId = $this->meetingRepository->createAndGetId($newMeeting);
        if ($newMeetingId === 0) {
            throw ApiException::internalServerError("No se pudo crear la reunión.");
        }
        
        $savedMeeting = $this->findMeetingOrFail($newMeetingId);
        $response = $this->buildMeetingResponse($savedMeeting);

        return Mapper::mapToDto(NewMeetingCreated::class, $response);
    }

    public function findMeetingById(int $id): MeetingResponse {
        $meeting = $this->findMeetingOrFail($id);
        return $this->buildMeetingResponse($meeting);
    }

    public function findMeetingsByOrganizerId(int $organizerId): array {
        $meetings = $this->meetingRepository->findByOrganizerId($organizerId);
        
        $responseArray = [];
        foreach ($meetings as $meeting) {
            $responseArray[] = $this->buildMeetingResponse($meeting);
        }
        return $responseArray;
    }

    public function findMeetingsForUser(int $userId): array {
        $attendances = $this->attendeeRepository->findAllByUserId($userId);
        
        $responseArray = [];
        foreach ($attendances as $attendance) {
            $responseArray[] = $this->findMeetingById($attendance->meeting_id);
        }
        return $responseArray;
    }

    public function updateMeeting(int $id, UpdateMeeting $updateDto): MeetingUpdated {
        $meeting = $this->findMeetingOrFail($id);
        
        if ($updateDto->title !== null) $meeting->title = $updateDto->title;
        if ($updateDto->description !== null) $meeting->description = $updateDto->description;
        if ($updateDto->startDatetime !== null) $meeting->start_datetime = $updateDto->startDatetime;
        if ($updateDto->estimatedDuration !== null) $meeting->estimated_duration = $updateDto->estimatedDuration;
        if ($updateDto->type !== null) $meeting->type = $updateDto->type;

        $this->meetingRepository->update($meeting);

        $response = $this->buildMeetingResponse($meeting);
        return Mapper::mapToDto(MeetingUpdated::class, $response);
    }

    public function addAttendeeToMeeting(int $meetingId, AddAttendee $addDto): AttendeeAdded {
        $this->findMeetingOrFail($meetingId);
        $this->userService->findUserById($addDto->userId);

        $existingAttendee = $this->attendeeRepository->findByMeetingAndUser($meetingId, $addDto->userId);
        if ($existingAttendee) {
            throw ApiException::conflict("Usuario con id {$addDto->userId} ya es un asistente para la reunión {$meetingId}.");
        }

        $newAttendee = new MeetingAttendeeEntity();
        $newAttendee->meeting_id = $meetingId;
        $newAttendee->user_id = $addDto->userId;
        $newAttendee->attended = 0;

        $newAttendeeId = $this->attendeeRepository->createAndGetId($newAttendee);
        if ($newAttendeeId === 0) {
            throw ApiException::internalServerError("No se pudo agregar al asistente.");
        }
        
        $savedAttendee = $this->findAttendeeOrFailById($newAttendeeId);
        $response = $this->buildAttendeeResponse($savedAttendee);
        
        return Mapper::mapToDto(AttendeeAdded::class, $response);
    }

    public function removeAttendeeFromMeeting(int $meetingId, int $userId): void {
        $attendee = $this->findAttendeeOrFail($meetingId, $userId);
        $this->attendeeRepository->delete($attendee->id);
    }

    public function confirmAttendance(int $meetingId, int $userId, ConfirmAttendance $confirmDto): AttendanceConfirmed {
        $attendee = $this->findAttendeeOrFail($meetingId, $userId);

        $attendee->attended = $confirmDto->attended;
        if ($confirmDto->comments !== null) {
            $attendee->comments = $confirmDto->comments;
        }

        $this->attendeeRepository->update($attendee);
        
        $response = $this->buildAttendeeResponse($attendee);
        return Mapper::mapToDto(AttendanceConfirmed::class, $response);
    }

    private function findMeetingOrFail(int $id): MeetingEntity {
        $meeting = $this->meetingRepository->findById($id);
        if (!$meeting) {
            throw ApiException::notFound("Reunión con id {$id} no encontrada.");
        }
        return $meeting;
    }

    private function findAttendeeOrFail(int $meetingId, int $userId): MeetingAttendeeEntity {
        $attendee = $this->attendeeRepository->findByMeetingAndUser($meetingId, $userId);
        if (!$attendee) {
            throw ApiException::notFound("Usuario con id {$userId} no es un asistente de la reunión {$meetingId}.");
        }
        return $attendee;
    }
    
    private function findAttendeeOrFailById(int $attendeeId): MeetingAttendeeEntity {
        $attendee = $this->attendeeRepository->findById($attendeeId);
        if (!$attendee) {
            throw ApiException::notFound("Asistente con id {$attendeeId} no encontrado.");
        }
        return $attendee;
    }

    private function buildMeetingResponse(MeetingEntity $meeting): MeetingResponse {
        $response = Mapper::mapToDto(MeetingResponse::class, $meeting);

        $response->organizer = $this->userService->findUserById($meeting->organizer_id);
        $attendees = $this->attendeeRepository->findAllByMeetingId($meeting->id);
        
        $attendeeDtos = [];
        foreach ($attendees as $attendee) {
            $attendeeDtos[] = $this->buildAttendeeResponse($attendee);
        }
        $response->attendees = $attendeeDtos;

        return $response;
    }

    private function buildAttendeeResponse(MeetingAttendeeEntity $attendee): MeetingAttendeeResponse {
        $response = Mapper::mapToDto(MeetingAttendeeResponse::class, $attendee);
        $response->meetingId = $attendee->meeting_id;
        $response->user = $this->userService->findUserById($attendee->user_id);
        return $response;
    }
}