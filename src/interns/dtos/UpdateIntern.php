<?php

namespace App\interns\dtos;

class UpdateIntern {
    public ?string $university = null;
    public ?string $career = null;
    public ?string $internshipStartDate = null;
    public ?string $internshipEndDate = null;
    public ?int $supervisorId = null;
    public ?bool $active = null;
}