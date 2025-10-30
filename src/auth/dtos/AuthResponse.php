<?php

namespace App\auth\dtos;

use App\users\dtos\UserResponse;

class AuthResponse {
    public string $accessToken;
    public UserResponse $user;
}