<?php

namespace App\interns;

use App\core\ApiException;
use App\interns\dtos\AssignSupervisor;
use App\interns\dtos\CreateIntern;
use App\interns\dtos\UpdateIntern;

class InternValidator {

    public static function validateCreate(array $data): CreateIntern {
        if (empty($data['userId']) || !is_numeric($data['userId'])) {
            throw ApiException::badRequest('El campo userId es requerido y debe ser un número.');
        }
        if (empty($data['university']) || !is_string($data['university'])) {
            throw ApiException::badRequest('El campo university es requerido.');
        }
        if (empty($data['career']) || !is_string($data['career'])) {
            throw ApiException::badRequest('El campo career es requerido.');
        }
        if (empty($data['internshipStartDate']) || !self::isDateString($data['internshipStartDate'])) {
            throw ApiException::badRequest('El campo internshipStartDate es requerido y debe ser una fecha válida (YYYY-MM-DD).');
        }

        $dto = new CreateIntern();
        $dto->userId = (int)$data['userId'];
        $dto->university = $data['university'];
        $dto->career = $data['career'];
        $dto->internshipStartDate = $data['internshipStartDate'];

        if (isset($data['internshipEndDate'])) {
            if (!self::isDateString($data['internshipEndDate'])) {
                throw ApiException::badRequest('El campo internshipEndDate debe ser una fecha válida (YYYY-MM-DD).');
            }
            $dto->internshipEndDate = $data['internshipEndDate'];
        }
        
        if (isset($data['supervisorId'])) {
            if (!is_numeric($data['supervisorId'])) {
                throw ApiException::badRequest('El campo supervisorId debe ser un número.');
            }
            $dto->supervisorId = (int)$data['supervisorId'];
        }
        
        return $dto;
    }

    public static function validateUpdate(array $data): UpdateIntern {
        $dto = new UpdateIntern();

        if (isset($data['university'])) $dto->university = $data['university'];
        if (isset($data['career'])) $dto->career = $data['career'];
        
        if (isset($data['internshipStartDate'])) {
            if (!self::isDateString($data['internshipStartDate'])) {
                throw ApiException::badRequest('internshipStartDate debe ser una fecha válida (YYYY-MM-DD).');
            }
            $dto->internshipStartDate = $data['internshipStartDate'];
        }
        
        if (isset($data['internshipEndDate'])) {
            if (!self::isDateString($data['internshipEndDate'])) {
                throw ApiException::badRequest('internshipEndDate debe ser una fecha válida (YYYY-MM-DD).');
            }
            $dto->internshipEndDate = $data['internshipEndDate'];
        }

        if (isset($data['supervisorId'])) {
            if (!is_numeric($data['supervisorId'])) {
                throw ApiException::badRequest('supervisorId debe ser un número.');
            }
            $dto->supervisorId = (int)$data['supervisorId'];
        }

        if (isset($data['active'])) {
            if (!is_bool($data['active'])) {
                throw ApiException::badRequest('El campo active debe ser un booleano.');
            }
            $dto->active = $data['active'];
        }

        return $dto;
    }

    public static function validateAssignSupervisor(array $data): AssignSupervisor {
        if (empty($data['internId']) || !is_numeric($data['internId'])) {
            throw ApiException::badRequest('El campo internId es requerido y debe ser un número.');
        }
        if (empty($data['supervisorId']) || !is_numeric($data['supervisorId'])) {
            throw ApiException::badRequest('El campo supervisorId es requerido y debe ser un número.');
        }

        $dto = new AssignSupervisor();
        $dto->internId = (int)$data['internId'];
        $dto->supervisorId = (int)$data['supervisorId'];
        return $dto;
    }

    private static function isDateString(string $date): bool {
        return (bool)strtotime($date);
    }
}