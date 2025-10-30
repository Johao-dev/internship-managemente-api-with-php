<?php

namespace App\messaging\dtos;

class SendMessage {
    public string $title;
    public string $content;
    public int $remitentId;
    public string $recipientType;
}