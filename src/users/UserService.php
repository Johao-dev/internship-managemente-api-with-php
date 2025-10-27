<?php

namespace App\users;

use App\core\ApiException;
use App\core\Mapper;
use App\users\dtos\AssignSupervisorRole;
use App\users\dtos\CreateUser;
use App\users\dtos\NewUserCreated;
use App\users\dtos\UpdateUser;
use App\users\dtos\UserDeleted;
use App\users\dtos\UserResponse;
use App\users\dtos\UserUpdatedResponse;

class UserService {

    private UserRepository $userRepository;
    private SupervisorRepository $supervisorRepository;

    public function __construct() {
        $this->userRepository = new UserRepository();
        $this->supervisorRepository = new SupervisorRepository();
    }

    public function createUser(CreateUser $createUserDto): NewUserCreated {
        $existingUser = $this->userRepository->findByEmail($createUserDto->institutionalEmail);
        if ($existingUser) {
            throw ApiException::conflict("Usuario con email {$createUserDto->institutionalEmail} ya existe.");
        }

        $hashedPassword = $this->hashPassword($createUserDto->password);
        
        $newUser = new UserEntity();
        $newUser->full_name = $createUserDto->fullName;
        $newUser->institutional_email = $createUserDto->institutionalEmail;
        $newUser->role = $createUserDto->role;
        $newUser->password = $hashedPassword;

        $success = $this->userRepository->create($newUser);
        if (!$success) {
            throw ApiException::internalServerError("No se pudo crear el usuario.");
        }

        $savedUser = $this->userRepository->findByEmail($newUser->institutional_email);

        return Mapper::mapToDto(NewUserCreated::class, $savedUser);
    }

    public function findUserById(int $id): UserResponse {
        $user = $this->findUserOrFail($id);
        return Mapper::mapToDto(UserResponse::class, $user);
    }

    public function findUserByEmail(string $email): UserResponse {
        $user = $this->userRepository->findByEmail($email);
        if (!$user) {
            throw ApiException::notFound("Usuario con email {$email} no encontrado.");
        }
        return Mapper::mapToDto(UserResponse::class, $user);
    }

    public function findAllUsers(): array {
        $users = $this->userRepository->findAll();
        return Mapper::mapToDtoArray(UserResponse::class, $users);
    }

    public function findAllActiveUsers(): array {
        $users = $this->userRepository->findAllActive();
        return Mapper::mapToDtoArray(UserResponse::class, $users);
    }

    public function findUsersByRole(UserRole $role): array {
        $users = $this->userRepository->findByRole($role);
        return Mapper::mapToDtoArray(UserResponse::class, $users);
    }

    public function updateUserProfile(int $id, UpdateUser $updateUserDto): UserUpdatedResponse {
        $user = $this->findUserOrFail($id);

        if (isset($updateUserDto->fullName)) {
            $user->full_name = $updateUserDto->fullName;
        }
        if (isset($updateUserDto->institutionalEmail)) {
            $user->institutional_email = $updateUserDto->institutionalEmail;
        }

        $this->userRepository->update($user);

        return Mapper::mapToDto(UserUpdatedResponse::class, $user);
    }

    public function deactivateUser(int $id): UserDeleted {
        $this->findUserOrFail($id);
        
        $success = $this->userRepository->delete($id);
        if (!$success) {
            throw ApiException::internalServerError("No se pudo desactivar el usuario.");
        }

        return new UserDeleted(true, "User deactivated successfully!", $id);
    }

    public function assignSupervisorRole(AssignSupervisorRole $assignDto): UserResponse {
        $user = $this->findUserOrFail($assignDto->userId);

        $existingSupervisor = $this->supervisorRepository->findByUserId($assignDto->userId);
        if ($existingSupervisor) {
            throw ApiException::conflict("Usuario con id {$assignDto->userId} ya tiene un perfil de supervisor.");
        }

        $newSupervisor = new SupervisorEntity();
        $newSupervisor->user_id = $assignDto->userId;
        $newSupervisor->area = $assignDto->area;
        
        $this->supervisorRepository->create($newSupervisor);

        $user->role = UserRole::SUPERVISOR->value;
        $this->userRepository->update($user);

        return Mapper::mapToDto(UserResponse::class, $user);
    }

    private function hashPassword(string $password): string {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
    }

    private function findUserOrFail(int $id): UserEntity {
        $user = $this->userRepository->findById($id);
        if (!$user) {
            throw ApiException::notFound("Usuario con id {$id} no encontrado.");
        }
        return $user;
    }
}
