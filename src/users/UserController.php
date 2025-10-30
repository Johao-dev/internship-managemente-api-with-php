<?php

namespace App\users;

use App\core\ApiException;
use App\core\AuthenticatedUserHandler;

class UserController {

    private UserService $userService;
    
    public function __construct() {
        $this->userService = new UserService();
    }

    public function getUserById(int $id) {
        $currentUser = AuthenticatedUserHandler::getUser();
        
        if ($currentUser->id !== $id && $currentUser->role !== UserRole::ADMIN->value) {
            throw ApiException::forbidden("No estás autorizado para ver este perfil de usuario.");
        }

        $userResponse = $this->userService->findUserById($id);
        return [
            'success' => true,
            'message' => 'Usuario encontrado.',
            'data' => $userResponse
        ];
    }

    public function getUserByEmail(string $email) {
        $currentUser = AuthenticatedUserHandler::getUser();
        $userToFind = $this->userService->findUserByEmail($email);

        if ($currentUser->id !== $userToFind->id && $currentUser->role !== UserRole::ADMIN->value) {
            throw ApiException::forbidden("No estás autorizado para ver este perfil de usuario.");
        }

        return [
            'success' => true,
            'message' => 'Usuario encontrado.',
            'data' => $userToFind
        ];
    }

    public function getAllUsers() {
        $users = $this->userService->findAllUsers();
        return [
            'success' => true,
            'message' => 'Usuarios recuperados.',
            'data' => $users
        ];
    }

    public function updateUser(int $id) {
        $currentUser = AuthenticatedUserHandler::getUser();

        if ($currentUser->id !== $id && $currentUser->role !== UserRole::ADMIN->value) {
            throw ApiException::forbidden("No estás autorizado para actualizar este perfil de usuario.");
        }
        
        $data = (array) json_decode(file_get_contents('php://input'), true);
        $updateDto = UserValidator::validateUpdate($data);

        $updatedUser = $this->userService->updateUserProfile($id, $updateDto);
        return [
            'success' => true,
            'message' => 'Usuario actualizado exitosamente.',
            'data' => $updatedUser
        ];
    }

    public function deactivateUser(int $id) {
        $currentUser = AuthenticatedUserHandler::getUser();

        if ($currentUser->id !== $id && $currentUser->role !== UserRole::ADMIN->value) {
            throw ApiException::forbidden("No estás autorizado para desactivar esta cuenta de usuario.");
        }

        $deactivatedUserResponse = $this->userService->deactivateUser($id);
        return [
            'success' => true,
            'message' => $deactivatedUserResponse->message,
            'data' => $deactivatedUserResponse
        ];
    }
    
    public function assignSupervisorRole() {
        $data = (array) json_decode(file_get_contents('php://input'), true);
        $dto = UserValidator::validateAssignSupervisorRole($data);

        $userResponse = $this->userService->assignSupervisorRole($dto);
        return [
            'success' => true,
            'message' => 'Rol de supervisor asignado exitosamente.',
            'data' => $userResponse
        ];
    }

    public function createUser() {
        $data = (array) json_decode(file_get_contents('php://input'), true);
        $dto = UserValidator::validateCreate($data);

        $newUser = $this->userService->createUser($dto);
        
        http_response_code(201);
        return [
            'success' => true,
            'message' => 'Usuario creado exitosamente.',
            'data' => $newUser
        ];
    }
}