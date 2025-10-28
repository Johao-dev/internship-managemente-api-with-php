<?php

namespace App\interns\dtos;

use App\users\dtos\UserResponse;
use App\users\dtos\SupervisorResponse;

class InternResponse {
    public int $id;
    public string $university;
    public string $career;
    public string $internshipStartDate;
    public ?string $internshipEndDate = null;
    public bool $active;

    public UserResponse $user;
    public ?SupervisorResponse $supervisor = null;
}