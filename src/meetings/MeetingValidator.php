<?php

namespace App\meetings;

use App\core\ApiException;
use App\meetings\dtos\AddAttendee;
use App\meetings\dtos\ConfirmAttendance;
use App\meetings\dtos\CreateMeeting;
use App\meetings\dtos\UpdateMeeting;

class MeetingValidator {

    public static function validateCreate(array $data): CreateMeeting {
        if (empty($data['title']) || !is_string($data['title']) || strlen($data['title']) > 255) {
            throw ApiException::badRequest('El campo title es requerido y debe ser menor a 255 caracteres.');
        }
        if (empty($data['startDatetime']) || !self::isDateString($data['startDatetime'])) {
            throw ApiException::badRequest('El campo startDatetime es requerido y debe ser una fecha válida (YYYY-MM-DD HH:MM:SS).');
        }
        if (empty($data['estimatedDuration']) || !is_numeric($data['estimatedDuration']) || $data['estimatedDuration'] < 1) {
            throw ApiException::badRequest('El campo estimatedDuration es requerido y debe ser un número mayor a 0.');
        }
        if (empty($data['type']) || !MeetingType::tryFrom($data['type'])) {
            throw ApiException::badRequest('El campo type es requerido y debe ser (presential, virtual).');
        }
        if (empty($data['organizerId']) || !is_numeric($data['organizerId'])) {
            throw ApiException::badRequest('El campo organizerId es requerido.');
        }

        $dto = new CreateMeeting();
        $dto->title = $data['title'];
        $dto->startDatetime = $data['startDatetime'];
        $dto->estimatedDuration = (int)$data['estimatedDuration'];
        $dto->type = $data['type'];
        $dto->organizerId = (int)$data['organizerId'];

        if (!empty($data['description']) && is_string($data['description'])) {
            $dto->description = $data['description'];
        }
        
        return $dto;
    }

    public static function validateUpdate(array $data): UpdateMeeting {
        $dto = new UpdateMeeting();

        if (isset($data['title'])) {
            if (!is_string($data['title']) || strlen($data['title']) > 255) {
                throw ApiException::badRequest('El campo title debe ser un string menor a 255 caracteres.');
            }
            $dto->title = $data['title'];
        }
        if (isset($data['description'])) {
            if (!is_string($data['description'])) {
                throw ApiException::badRequest('El campo description debe ser un string.');
            }
            $dto->description = $data['description'];
        }
        if (isset($data['startDatetime'])) {
            if (!self::isDateString($data['startDatetime'])) {
                throw ApiException::badRequest('El campo startDatetime debe ser una fecha válida.');
            }
            $dto->startDatetime = $data['startDatetime'];
        }
        if (isset($data['estimatedDuration'])) {
            if (!is_numeric($data['estimatedDuration']) || $data['estimatedDuration'] < 1) {
                throw ApiException::badRequest('El campo estimatedDuration debe ser un número mayor a 0.');
            }
            $dto->estimatedDuration = (int)$data['estimatedDuration'];
        }
        if (isset($data['type'])) {
            if (!MeetingType::tryFrom($data['type'])) {
                throw ApiException::badRequest('El campo type debe ser (presential, virtual).');
            }
            $dto->type = $data['type'];
        }

        return $dto;
    }

    public static function validateAddAttendee(array $data): AddAttendee {
        if (empty($data['userId']) || !is_numeric($data['userId'])) {
            throw ApiException::badRequest('El campo userId es requerido y debe ser un número.');
        }
        $dto = new AddAttendee();
        $dto->userId = (int)$data['userId'];
        return $dto;
    }

    public static function validateConfirmAttendance(array $data): ConfirmAttendance {
        if (!isset($data['attended']) || !is_bool($data['attended'])) {
            throw ApiException::badRequest('El campo attended es requerido y debe ser booleano.');
        }
        
        $dto = new ConfirmAttendance();
        $dto->attended = $data['attended'];

        if (isset($data['comments'])) {
            if (!is_string($data['comments'])) {
                throw ApiException::badRequest('El campo comments debe ser un string.');
            }
            $dto->comments = $data['comments'];
        }

        return $dto;
    }

    private static function isDateString(string $date): bool {
        return (bool)strtotime($date);
    }
}