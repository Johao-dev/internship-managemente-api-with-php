<?php

namespace App\users\dtos;

class CreateUser {
    public string $fullName;
    public string $institutionalEmail;
    public string $role;
    public string $password;
}