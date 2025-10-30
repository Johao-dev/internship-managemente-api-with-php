<?php

namespace App\messaging\dtos;

use App\users\dtos\UserResponse;

class MessageRecipientResponse {
    public int $id;
    public int $messageId;
    public int $userId;
    public bool $readed;
    public ?string $readDate = null;
    public UserResponse $user;
}