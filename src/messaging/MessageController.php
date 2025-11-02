<?php

namespace App\messaging;

use App\core\ApiException;
use App\core\AuthenticatedUserHandler;
use App\users\UserRole;

class MessageController {

    private MessageService $messagingService;
    private MessageValidator $validator;

    public function __construct() {
        $this->messagingService = new MessageService();
        $this->validator = new MessageValidator();
    }

    public function sendMessage() {
        $currentUser = AuthenticatedUserHandler::getUser();
        $data = (array) json_decode(file_get_contents('php://input'), true);
        $dto = $this->validator->validateSend($data);

        if ($dto->remitentId !== $currentUser->id) {
            throw ApiException::forbidden("No estás autorizado para enviar un mensaje como este remitente.");
        }

        $sentMessage = $this->messagingService->sendMessage($dto);
        
        http_response_code(201);
        return [
            'success' => true,
            'message' => 'Mensaje enviado exitosamente.',
            'data' => $sentMessage
        ];
    }

    public function findMessageById(int $id) {
        $currentUser = AuthenticatedUserHandler::getUser();
        $message = $this->messagingService->findMessageById($id);

        $isAdmin = $currentUser->role === UserRole::ADMIN->value;
        $isRemitent = $currentUser->id === $message->remitent->id;
        
        $isRecipient = false;
        foreach ($message->recipients as $recipient) {
            if ($recipient->user->id === $currentUser->id) {
                $isRecipient = true;
                break;
            }
        }

        if (!$isAdmin && !$isRemitent && !$isRecipient) {
            throw ApiException::forbidden("No estás autorizado para ver este mensaje.");
        }

        return [
            'success' => true,
            'message' => 'Mensaje encontrado.',
            'data' => $message
        ];
    }

    public function findMessagesByRemitentId(int $remitentId) {
        $currentUser = AuthenticatedUserHandler::getUser();

        if ($currentUser->role !== UserRole::ADMIN->value && $currentUser->id !== $remitentId) {
            throw ApiException::forbidden("No estás autorizado para ver los mensajes de este remitente.");
        }

        $messages = $this->messagingService->findMessagesByRemitentId($remitentId);
        return [
            'success' => true,
            'message' => 'Mensajes encontrados.',
            'data' => $messages
        ];
    }

    public function findInboxForUser(int $userId) {
        $currentUser = AuthenticatedUserHandler::getUser();

        if ($currentUser->role !== UserRole::ADMIN->value && $currentUser->id !== $userId) {
            throw ApiException::forbidden("No estás autorizado para ver la bandeja de entrada de este usuario.");
        }

        $messages = $this->messagingService->findInboxForUser($userId);
        return [
            'success' => true,
            'message' => 'Bandeja de entrada recuperada.',
            'data' => $messages
        ];
    }

    public function getUnreadCount(int $userId) {
        $currentUser = AuthenticatedUserHandler::getUser();

        if ($currentUser->role !== UserRole::ADMIN->value && $currentUser->id !== $userId) {
            throw ApiException::forbidden("No estás autorizado para ver el contador de este usuario.");
        }

        $count = $this->messagingService->getUnreadCountForUser($userId);
        return [
            'success' => true,
            'message' => 'Contador recuperado.',
            'data' => ['count' => $count]
        ];
    }

    public function markMessageAsRead(int $id) {
        $currentUser = AuthenticatedUserHandler::getUser();
        $data = (array) json_decode(file_get_contents('php://input'), true);
        $dto = $this->validator->validateMarkAsRead($data);

        if ($currentUser->role !== UserRole::ADMIN->value && $dto->userId !== $currentUser->id) {
            throw ApiException::forbidden("No estás autorizado para marcar este mensaje como leído.");
        }

        $response = $this->messagingService->markMessageAsRead($id, $dto);
        return [
            'success' => true,
            'message' => $response->message,
            'data' => $response
        ];
    }
}