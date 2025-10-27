<?php

namespace App\users\dtos;

class UserResponse {
    public int $id;
    public string $fullName;
    public string $institutionalEmail;
    public string $role;
    public bool $active;
}