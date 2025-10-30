<?php

namespace App\auth\dtos;

use App\users\dtos\NewUserCreated;

class RegisterResponse {
    public string $message;
    public NewUserCreated $user;
}