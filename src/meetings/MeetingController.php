<?php

namespace App\meetings;

use App\core\ApiException;
use App\core\AuthenticatedUserHandler;
use App\users\UserRole;

class MeetingController {

    private MeetingService $meetingService;
    private MeetingValidator $validator;

    public function __construct() {
        $this->meetingService = new MeetingService();
        $this->validator = new MeetingValidator();
    }

    public function createMeeting() {
        $currentUser = AuthenticatedUserHandler::getUser();
        $data = (array) json_decode(file_get_contents('php://input'), true);
        $dto = $this->validator->validateCreate($data);

        if ($dto->organizerId !== $currentUser->id) {
            throw ApiException::forbidden("No estás autorizado para crear una reunión como este organizador.");
        }

        $newMeeting = $this->meetingService->createMeeting($dto);
        
        http_response_code(201);
        return [
            'success' => true,
            'message' => 'Reunión creada exitosamente.',
            'data' => $newMeeting
        ];
    }

    public function findMeetingById(int $id) {
        $currentUser = AuthenticatedUserHandler::getUser();
        $meeting = $this->meetingService->findMeetingById($id);

        $isAdmin = $currentUser->role === UserRole::ADMIN->value;
        $isOrganizer = $currentUser->id === $meeting->organizer->id;
        
        $isAttendee = false;
        foreach ($meeting->attendees as $attendee) {
            if ($attendee->user->id === $currentUser->id) {
                $isAttendee = true;
                break;
            }
        }

        if (!$isAdmin && !$isOrganizer && !$isAttendee) {
            throw ApiException::forbidden("No estás autorizado para ver esta reunión.");
        }

        return [
            'success' => true,
            'message' => 'Reunión encontrada.',
            'data' => $meeting
        ];
    }

    public function findMeetingsByOrganizerId(int $organizerId) {
        $currentUser = AuthenticatedUserHandler::getUser();

        if ($currentUser->role !== UserRole::ADMIN->value && $currentUser->id !== $organizerId) {
            throw ApiException::forbidden("No estás autorizado para ver las reuniones de este organizador.");
        }

        $meetings = $this->meetingService->findMeetingsByOrganizerId($organizerId);
        return [
            'success' => true,
            'message' => 'Reuniones encontradas.',
            'data' => $meetings
        ];
    }

    public function findMeetingsForUser(int $userId) {
        $currentUser = AuthenticatedUserHandler::getUser();

        if ($currentUser->role !== UserRole::ADMIN->value && $currentUser->id !== $userId) {
            throw ApiException::forbidden("No estás autorizado para ver las reuniones de este usuario.");
        }

        $meetings = $this->meetingService->findMeetingsForUser($userId);
        return [
            'success' => true,
            'message' => 'Reuniones encontradas.',
            'data' => $meetings
        ];
    }

    public function updateMeeting(int $id) {
        $currentUser = AuthenticatedUserHandler::getUser();
        $meeting = $this->meetingService->findMeetingById($id);

        $isAdmin = $currentUser->role === UserRole::ADMIN->value;
        $isOrganizer = $currentUser->id === $meeting->organizer->id;

        if (!$isAdmin && !$isOrganizer) {
            throw ApiException::forbidden("No estás autorizado para actualizar esta reunión.");
        }

        $data = (array) json_decode(file_get_contents('php://input'), true);
        $dto = $this->validator->validateUpdate($data);

        $updatedMeeting = $this->meetingService->updateMeeting($id, $dto);
        return [
            'success' => true,
            'message' => 'Reunión actualizada.',
            'data' => $updatedMeeting
        ];
    }

    public function addAttendee(int $id) {
        $currentUser = AuthenticatedUserHandler::getUser();
        $meeting = $this->meetingService->findMeetingById($id);

        $isAdmin = $currentUser->role === UserRole::ADMIN->value;
        $isOrganizer = $currentUser->id === $meeting->organizer->id;

        if (!$isAdmin && !$isOrganizer) {
            throw ApiException::forbidden("No estás autorizado para agregar asistentes a esta reunión.");
        }

        $data = (array) json_decode(file_get_contents('php://input'), true);
        $dto = $this->validator->validateAddAttendee($data);
        
        $addedAttendee = $this->meetingService->addAttendeeToMeeting($id, $dto);
        return [
            'success' => true,
            'message' => 'Asistente agregado.',
            'data' => $addedAttendee
        ];
    }

    public function removeAttendee(int $id, int $userId) {
        $currentUser = AuthenticatedUserHandler::getUser();
        $meeting = $this->meetingService->findMeetingById($id);

        $isAdmin = $currentUser->role === UserRole::ADMIN->value;
        $isOrganizer = $currentUser->id === $meeting->organizer->id;

        if (!$isAdmin && !$isOrganizer) {
            throw ApiException::forbidden("No estás autorizado para eliminar asistentes de esta reunión.");
        }

        $this->meetingService->removeAttendeeFromMeeting($id, $userId);
        
        http_response_code(200);
        return [
            'success' => true,
            'message' => 'Asistente eliminado.',
            'data' => null
        ];
    }

    public function confirmAttendance(int $id, int $userId) {
        $currentUser = AuthenticatedUserHandler::getUser();
        $meeting = $this->meetingService->findMeetingById($id);

        $isAdmin = $currentUser->role === UserRole::ADMIN->value;
        $isOrganizer = $currentUser->id === $meeting->organizer->id;
        $isSelf = $currentUser->id === $userId;

        if (!$isAdmin && !$isOrganizer && !$isSelf) {
            throw ApiException::forbidden("No estás autorizado para confirmar la asistencia de este usuario.");
        }
        
        if ($isSelf && !$isAdmin && !$isOrganizer) {
            $isAttendee = false;
            foreach ($meeting->attendees as $attendee) {
                if ($attendee->user->id === $userId) {
                    $isAttendee = true;
                    break;
                }
            }
            if (!$isAttendee) {
                throw ApiException::forbidden("El usuario no es un asistente de esta reunión.");
            }
        }

        $data = (array) json_decode(file_get_contents('php://input'), true);
        $dto = $this->validator->validateConfirmAttendance($data);
        
        $confirmed = $this->meetingService->confirmAttendance($id, $userId, $dto);
        return [
            'success' => true,
            'message' => 'Asistencia confirmada.',
            'data' => $confirmed
        ];
    }
}