<?php

namespace App\messaging\dtos;

class MessageMarkedRead {
    public bool $success;
    public string $message;
    public int $messageId;
    public int $userId;

    public function __construct(bool $success, string $message, int $messageId, int $userId) {
        $this->success = $success;
        $this->message = $message;
        $this->messageId = $messageId;
        $this->userId = $userId;
    }
}