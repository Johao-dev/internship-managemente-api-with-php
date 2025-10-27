<?php

namespace App\users;

use App\core\ApiException;
use App\users\dtos\AssignSupervisorRole;
use App\users\dtos\CreateUser;
use App\users\dtos\UpdateUser;

class UserValidator {

    public static function validateCreate(array $data): CreateUser {
        if (empty($data['fullName'])) {
            throw ApiException::badRequest('El campo fullName es requerido.');
        }
        if (empty($data['institutionalEmail']) || !filter_var($data['institutionalEmail'], FILTER_VALIDATE_EMAIL)) {
            throw ApiException::badRequest('El campo institutionalEmail es requerido y debe ser un email válido.');
        }
        if (empty($data['role']) || !UserRole::tryFrom($data['role'])) {
            throw ApiException::badRequest('El campo role es requerido y debe ser uno de: ADMIN, SUPERVISOR, INTERN.');
        }
        if (empty($data['password']) || strlen($data['password']) < 12) {
            throw ApiException::badRequest('El campo password es requerido y debe tener al menos 12 caracteres.');
        }

        $dto = new CreateUser();
        $dto->fullName = $data['fullName'];
        $dto->institutionalEmail = $data['institutionalEmail'];
        $dto->role = $data['role'];
        $dto->password = $data['password'];
        return $dto;
    }

    public static function validateUpdate(array $data): UpdateUser {
        $dto = new UpdateUser();
        
        if (isset($data['fullName'])) {
            if (!is_string($data['fullName'])) {
                throw ApiException::badRequest('El campo fullName debe ser un string.');
            }
            $dto->fullName = $data['fullName'];
        }
        
        if (isset($data['institutionalEmail'])) {
            if (!filter_var($data['institutionalEmail'], FILTER_VALIDATE_EMAIL)) {
                throw ApiException::badRequest('El campo institutionalEmail debe ser un email válido.');
            }
            $dto->institutionalEmail = $data['institutionalEmail'];
        }
        
        return $dto;
    }

    public static function validateAssignSupervisorRole(array $data): AssignSupervisorRole {
        if (empty($data['userId']) || !is_numeric($data['userId'])) {
            throw ApiException::badRequest('El campo userId es requerido y debe ser un número.');
        }
        if (empty($data['area']) || !is_string($data['area'])) {
            throw ApiException::badRequest('El campo area es requerido y debe ser un string.');
        }
        
        $dto = new AssignSupervisorRole();
        $dto->userId = (int)$data['userId'];
        $dto->area = $data['area'];
        return $dto;
    }
}