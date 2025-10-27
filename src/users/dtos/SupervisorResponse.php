<?php

namespace App\users\dtos;

class SupervisorResponse {
    public int $id;
    public string $area;
    public UserResponse $user;
}