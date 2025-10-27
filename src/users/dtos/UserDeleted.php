<?php

namespace App\users\dtos;

class UserDeleted {
    public bool $success;
    public string $message;
    public int $deletedUserId;

    public function __construct(bool $success, string $message, int $deletedUserId) {
        $this->success = $success;
        $this->message = $message;
        $this->deletedUserId = $deletedUserId;
    }
}