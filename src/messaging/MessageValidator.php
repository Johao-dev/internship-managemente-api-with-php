<?php

namespace App\messaging;

use App\core\ApiException;
use App\messaging\dtos\MarkMessageAsRead;
use App\messaging\dtos\SendMessage;

class MessagingValidator {

    public static function validateSend(array $data): SendMessage {
        if (empty($data['title']) || !is_string($data['title'])) {
            throw ApiException::badRequest('El campo title es requerido.');
        }
        if (empty($data['content']) || !is_string($data['content'])) {
            throw ApiException::badRequest('El campo content es requerido.');
        }
        if (empty($data['remitentId']) || !is_numeric($data['remitentId'])) {
            throw ApiException::badRequest('El campo remitentId es requerido y debe ser un número.');
        }
        if (empty($data['recipientType']) || !RecipientType::tryFrom($data['recipientType'])) {
            throw ApiException::badRequest('El campo recipientType es requerido y debe ser (intern, supervisor, all).');
        }

        $dto = new SendMessage();
        $dto->title = $data['title'];
        $dto->content = $data['content'];
        $dto->remitentId = (int)$data['remitentId'];
        $dto->recipientType = $data['recipientType'];

        return $dto;
    }

    public static function validateMarkAsRead(array $data): MarkMessageAsRead {
        if (empty($data['userId']) || !is_numeric($data['userId'])) {
            throw ApiException::badRequest('El campo userId es requerido y debe ser un número.');
        }

        $dto = new MarkMessageAsRead();
        $dto->userId = (int)$data['userId'];
        return $dto;
    }
}
