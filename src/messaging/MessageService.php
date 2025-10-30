<?php

namespace App\messaging;

use App\core\ApiException;
use App\core\Mapper;
use App\messaging\dtos\MarkMessageAsRead;
use App\messaging\dtos\MessageMarkedRead;
use App\messaging\dtos\MessageResponse;
use App\messaging\dtos\MessageRecipientResponse;
use App\messaging\dtos\SendMessage;
use App\messaging\dtos\SentMessage;
use App\users\UserService;
use App\users\UserRole;

class MessagingService {

    private MessageRepository $messageRepository;
    private MessageRecipientRepository $recipientRepository;
    private UserService $userService;

    public function __construct() {
        $this->messageRepository = new MessageRepository();
        $this->recipientRepository = new MessageRecipientRepository();
        $this->userService = new UserService();
    }

    public function sendMessage(SendMessage $sendDto): SentMessage {
        $newMessage = new MessageEntity();
        $newMessage->title = $sendDto->title;
        $newMessage->content = $sendDto->content;
        $newMessage->recipient_type = $sendDto->recipientType;
        $newMessage->remitent_id = $sendDto->remitentId;

        $newMsgId = $this->messageRepository->createAndGetId($newMessage);
        if ($newMsgId === 0) {
            throw ApiException::internalServerError("No se pudo guardar el mensaje.");
        }

        $targetUsers = [];
        switch ($sendDto->recipientType) {
            case RecipientType::ALL->value:
                $targetUsers = $this->userService->findAllActiveUsers();
                break;
            case RecipientType::INTERN->value:
                $targetUsers = $this->userService->findUsersByRole(UserRole::INTERN);
                break;
            case RecipientType::SUPERVISOR->value:
                $targetUsers = $this->userService->findUsersByRole(UserRole::SUPERVISOR);
                break;
        }

        foreach ($targetUsers as $userDto) {
            $recipientEntry = new MessageRecipientEntity();
            $recipientEntry->message_id = $newMsgId;
            $recipientEntry->user_id = $userDto->id;
            $recipientEntry->readed = 0;
            
            $this->recipientRepository->create($recipientEntry);
        }

        $savedMessage = $this->findMessageOrFail($newMsgId);
        $response = $this->buildMessageResponse($savedMessage);

        return Mapper::mapToDto(SentMessage::class, $response);
    }

    public function findMessageById(int $id): MessageResponse {
        $message = $this->findMessageOrFail($id);
        return $this->buildMessageResponse($message);
    }

    public function findMessagesByRemitentId(int $remitentId): array {
        $messages = $this->messageRepository->findByRemitentId($remitentId);
        
        $responseArray = [];
        foreach ($messages as $message) {
            $responseArray[] = $this->buildMessageResponse($message);
        }
        return $responseArray;
    }

    public function findInboxForUser(int $userId): array {
        $messages = $this->messageRepository->findInboxByUserId($userId);
        
        $responseArray = [];
        foreach ($messages as $message) {
            $responseArray[] = $this->buildMessageResponse($message);
        }
        return $responseArray;
    }

    public function getUnreadCountForUser(int $userId): int {
        $unreadMessages = $this->messageRepository->findUnreadByUserId($userId);
        return count($unreadMessages);
    }

    public function markMessageAsRead(int $messageId, MarkMessageAsRead $markDto): MessageMarkedRead {
        $userId = $markDto->userId;
        $this->findRecipientEntryOrFail($messageId, $userId);

        $success = $this->recipientRepository->markAsRead($messageId, $userId);
        if (!$success) {
            throw ApiException::internalServerError("No se pudo marcar el mensaje como leído.");
        }

        return new MessageMarkedRead(true, "Mensaje marcado como leído.", $messageId, $userId);
    }

    private function findMessageOrFail(int $id): MessageEntity {
        $message = $this->messageRepository->findById($id);
        if (!$message) {
            throw ApiException::notFound("Mensaje con id {$id} no encontrado.");
        }
        return $message;
    }

    private function findRecipientEntryOrFail(int $messageId, int $userId): MessageRecipientEntity {
        $recipient = $this->recipientRepository->findByMessageAndUser($messageId, $userId);
        if (!$recipient) {
            throw ApiException::notFound("Usuario con id {$userId} no es destinatario del mensaje {$messageId}.");
        }
        return $recipient;
    }

    private function buildMessageResponse(MessageEntity $message): MessageResponse {
        $response = Mapper::mapToDto(MessageResponse::class, $message);

        $response->remitent = $this->userService->findUserById($message->remitent_id);
        $recipients = $this->recipientRepository->findAllByMessageId($message->id);
        
        $recipientDtos = [];
        foreach ($recipients as $recipient) {
            $recipientDtos[] = $this->buildRecipientResponse($recipient);
        }
        $response->recipients = $recipientDtos;

        return $response;
    }

    private function buildRecipientResponse(MessageRecipientEntity $recipient): MessageRecipientResponse {
        $response = Mapper::mapToDto(MessageRecipientResponse::class, $recipient);
        $response->messageId = $recipient->message_id;
        $response->userId = $recipient->user_id;
        $response->user = $this->userService->findUserById($recipient->user_id);
        return $response;
    }
}