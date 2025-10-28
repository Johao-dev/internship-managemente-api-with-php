<?php

namespace App\interns\dtos;

class CreateIntern {
    public int $userId;
    public string $university;
    public string $career;
    public string $internshipStartDate;
    public ?string $internshipEndDate = null;
    public ?int $supervisorId = null;
}