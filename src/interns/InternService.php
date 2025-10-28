<?php

namespace App\interns;

use App\core\ApiException;
use App\core\Mapper;
use App\interns\dtos\AssignSupervisor;
use App\interns\dtos\CreateIntern;
use App\interns\dtos\InternDeleted;
use App\interns\dtos\InternResponse;
use App\interns\dtos\InternUpdatedResponse;
use App\interns\dtos\NewInternCreated;
use App\interns\dtos\UpdateIntern;
use App\users\dtos\SupervisorResponse;
use App\users\SupervisorEntity;
use App\users\SupervisorRepository;
use App\users\UserEntity;
use App\users\UserRepository;
use App\users\UserRole;

class InternService {

    private InternRepository $internRepository;
    private UserRepository $userRepository;
    private SupervisorRepository $supervisorRepository;

    public function __construct() {
        $this->internRepository = new InternRepository();
        $this->userRepository = new UserRepository();
        $this->supervisorRepository = new SupervisorRepository();
    }

    public function createIntern(CreateIntern $createDto): NewInternCreated {
        $this->validateInternUser($createDto->userId);
        $this->ensureNoExistingIntern($createDto->userId);

        if ($createDto->supervisorId) {
            $this->validateSupervisor($createDto->supervisorId);
        }

        $internEntity = new InternEntity();
        $internEntity->user_id = $createDto->userId;
        $internEntity->university = $createDto->university;
        $internEntity->career = $createDto->career;
        $internEntity->internship_start_date = $createDto->internshipStartDate;
        $internEntity->internship_end_date = $createDto->internshipEndDate;
        $internEntity->supervisor_id = $createDto->supervisorId;

        $this->internRepository->create($internEntity);

        $savedIntern = $this->internRepository->findByUserId($createDto->userId);
        $response = $this->buildInternResponse($savedIntern);

        return Mapper::mapToDto(NewInternCreated::class, $response);
    }

    public function findInternById(int $id): InternResponse {
        $intern = $this->findInternOrFail($id);
        return $this->buildInternResponse($intern);
    }

    public function findInternByUserId(int $userId): InternResponse {
        $intern = $this->internRepository->findByUserId($userId);
        if (!$intern) {
            throw ApiException::notFound("Perfil de practicante para usuario con id {$userId} no encontrado.");
        }
        return $this->buildInternResponse($intern);
    }

    public function findActiveInterns(): array {
        $interns = $this->internRepository->findAllActive();
        
        $responseArray = [];
        foreach ($interns as $intern) {
            $responseArray[] = $this->buildInternResponse($intern);
        }
        return $responseArray;
    }

    public function updateInternProfile(int $id, UpdateIntern $updateDto): InternUpdatedResponse {
        $intern = $this->findInternOrFail($id);
        
        if ($updateDto->supervisorId) {
            $this->validateSupervisor($updateDto->supervisorId);
        }

        if ($updateDto->university !== null) $intern->university = $updateDto->university;
        if ($updateDto->career !== null) $intern->career = $updateDto->career;
        if ($updateDto->internshipStartDate !== null) $intern->internship_start_date = $updateDto->internshipStartDate;
        if ($updateDto->internshipEndDate !== null) $intern->internship_end_date = $updateDto->internshipEndDate;
        if ($updateDto->supervisorId !== null) $intern->supervisor_id = $updateDto->supervisorId;
        if ($updateDto->active !== null) $intern->active = $updateDto->active;

        $this->internRepository->update($intern);
        
        $response = $this->buildInternResponse($intern);
        return Mapper::mapToDto(InternUpdatedResponse::class, $response);
    }

    public function assignSupervisorToIntern(AssignSupervisor $assignDto): InternResponse {
        $intern = $this->findInternOrFail($assignDto->internId);
        $this->validateSupervisor($assignDto->supervisorId);

        $intern->supervisor_id = $assignDto->supervisorId;
        $this->internRepository->update($intern);
        
        return $this->buildInternResponse($intern);
    }

    public function deactivateIntern(int $id): InternDeleted {
        $intern = $this->findInternOrFail($id);
        $this->internRepository->delete($id);

        return new InternDeleted(true, "Practicante con id {$id} ha sido desactivado.", $intern->id);
    }

    public function activateIntern(int $id): InternUpdatedResponse {
        $intern = $this->findInternOrFail($id);
        $intern->active = 1;
        $this->internRepository->update($intern);

        $response = $this->buildInternResponse($intern);
        return Mapper::mapToDto(InternUpdatedResponse::class, $response);
    }

    private function findInternOrFail(int $id): InternEntity {
        $intern = $this->internRepository->findById($id);
        if (!$intern) {
            throw ApiException::notFound("Practicante con id {$id} no encontrado.");
        }
        return $intern;
    }

    private function validateInternUser(int $userId): void {
        $user = $this->findUserOrFail($userId);
        if ($user->role !== UserRole::INTERN->value) {
            throw ApiException::badRequest("Usuario con id {$userId} no tiene el rol de INTERN.");
        }
    }

    private function validateSupervisor(int $supervisorId): SupervisorEntity {
        $supervisor = $this->supervisorRepository->findById($supervisorId);
        if (!$supervisor) {
            throw ApiException::notFound("Supervisor con id {$supervisorId} no encontrado.");
        }
        $user = $this->findUserOrFail($supervisor->user_id);
        if ($user->role !== UserRole::SUPERVISOR->value) {
            throw ApiException::badRequest("Usuario asociado al supervisor con id {$supervisorId} no tiene el rol de SUPERVISOR.");
        }
        return $supervisor;
    }

    private function ensureNoExistingIntern(int $userId): void {
        $existingIntern = $this->internRepository->findByUserId($userId);
        if ($existingIntern) {
            throw new ApiException("Ya existe un perfil de practicante para el usuario con id {$userId}.", 409);
        }
    }

    private function findUserOrFail(int $id): UserEntity {
        $user = $this->userRepository->findById($id);
        if (!$user) {
            throw ApiException::notFound("Usuario con id {$id} no encontrado.");
        }
        return $user;
    }

    private function buildInternResponse(InternEntity $intern): InternResponse {
        $response = Mapper::mapToDto(InternResponse::class, $intern);

        $userEntity = $this->findUserOrFail($intern->user_id);
        $response->user = Mapper::mapToDto(\App\users\dtos\UserResponse::class, $userEntity);

        if ($intern->supervisor_id) {
            $supervisorEntity = $this->supervisorRepository->findById($intern->supervisor_id);
            if ($supervisorEntity) {
                $supervisorUserEntity = $this->findUserOrFail($supervisorEntity->user_id);
                
                $supervisorDto = new SupervisorResponse();
                $supervisorDto->id = $supervisorEntity->id;
                $supervisorDto->area = $supervisorEntity->area;
                $supervisorDto->user = Mapper::mapToDto(\App\users\dtos\UserResponse::class, $supervisorUserEntity);
                
                $response->supervisor = $supervisorDto;
            }
        }

        return $response;
    }
}