<?php

namespace App\interns;

use App\core\ApiException;
use App\core\AuthenticatedUserHandler;
use App\users\UserRole;

class InternController {

    private InternService $internService;
    private InternValidator $internValidator;

    public function __construct() {
        $this->internService = new InternService();
        $this->internValidator = new InternValidator();
    }

    public function createIntern() {
        $data = (array) json_decode(file_get_contents('php://input'), true);
        $dto = $this->internValidator->validateCreate($data);

        $newIntern = $this->internService->createIntern($dto);

        http_response_code(201);
        return [
            'success' => true,
            'message' => 'Practicante creado exitosamente.',
            'data' => $newIntern
        ];
    }

    public function findInternById(int $id) {
        $currentUser = AuthenticatedUserHandler::getUser();
        $intern = $this->internService->findInternById($id);

        $isOwner = ($currentUser->id === $intern->user->id);
        $isSupervisor = ($intern->supervisor && $currentUser->id === $intern->supervisor->user->id);
        $isAdmin = ($currentUser->role === UserRole::ADMIN->value);

        if (!$isOwner && !$isSupervisor && !$isAdmin) {
            throw ApiException::forbidden("No estás autorizado para ver este perfil de practicante.");
        }

        return [
            'success' => true,
            'message' => 'Practicante encontrado.',
            'data' => $intern
        ];
    }

    public function findInternByUserId(int $userId) {
        $currentUser = AuthenticatedUserHandler::getUser();
        $intern = $this->internService->findInternByUserId($userId);

        $isOwner = ($currentUser->id === $userId);
        $isSupervisor = ($intern->supervisor && $currentUser->id === $intern->supervisor->user->id);
        $isAdmin = ($currentUser->role === UserRole::ADMIN->value);

        if (!$isOwner && !$isSupervisor && !$isAdmin) {
            throw ApiException::forbidden("No estás autorizado para ver este perfil de practicante.");
        }

        return [
            'success' => true,
            'message' => 'Practicante encontrado.',
            'data' => $intern
        ];
    }

    public function findAllActiveInterns() {
        $interns = $this->internService->findActiveInterns();
        return [
            'success' => true,
            'message' => 'Practicantes activos recuperados.',
            'data' => $interns
        ];
    }

    public function updateIntern(int $id) {
        $currentUser = AuthenticatedUserHandler::getUser();
        $intern = $this->internService->findInternById($id);

        $isOwner = ($currentUser->id === $intern->user->id);
        $isSupervisor = ($intern->supervisor && $currentUser->id === $intern->supervisor->user->id);
        $isAdmin = ($currentUser->role === UserRole::ADMIN->value);

        if (!$isOwner && !$isSupervisor && !$isAdmin) {
            throw ApiException::forbidden("No estás autorizado para actualizar este perfil.");
        }

        $data = (array) json_decode(file_get_contents('php://input'), true);
        $dto = $this->internValidator->validateUpdate($data);

        $updatedIntern = $this->internService->updateInternProfile($id, $dto);

        return [
            'success' => true,
            'message' => 'Perfil de practicante actualizado.',
            'data' => $updatedIntern
        ];
    }

    public function assignSupervisor(int $id) {
        $data = (array) json_decode(file_get_contents('php://input'), true);
        $dto = $this->internValidator->validateAssignSupervisor($data);

        $response = $this->internService->assignSupervisorToIntern($dto);
        return [
            'success' => true,
            'message' => 'Supervisor asignado exitosamente.',
            'data' => $response
        ];
    }

    public function deactivateIntern(int $id) {
        $currentUser = AuthenticatedUserHandler::getUser();
        $intern = $this->internService->findInternById($id);

        $isSupervisor = ($intern->supervisor && $currentUser->id === $intern->supervisor->user->id);
        $isAdmin = ($currentUser->role === UserRole::ADMIN->value);

        if (!$isSupervisor && !$isAdmin) {
            throw ApiException::forbidden("No estás autorizado para desactivar este perfil.");
        }

        $deletedResponse = $this->internService->deactivateIntern($id);
        return [
            'success' => true,
            'message' => $deletedResponse->message,
            'data' => $deletedResponse
        ];
    }

    public function activateIntern(int $id) {
        $activatedIntern = $this->internService->activateIntern($id);
        return [
            'success' => true,
            'message' => 'Practicante activado exitosamente.',
            'data' => $activatedIntern
        ];
    }
}