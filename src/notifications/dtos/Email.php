<?php

namespace App\notifications\dtos;

class Email {
    public string $toEmail;
    public string $toName;
    public string $subject;
    public string $body;
}