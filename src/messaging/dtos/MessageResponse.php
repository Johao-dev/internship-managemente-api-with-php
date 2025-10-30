<?php

namespace App\messaging\dtos;

use App\users\dtos\UserResponse;

class MessageResponse {
    public int $id;
    public string $title;
    public string $content;
    public string $recipientType;
    public string $sendDate;
    public bool $active;
    public UserResponse $remitent;
    
    /** @var MessageRecipientResponse[] */
    public array $recipients = [];
}