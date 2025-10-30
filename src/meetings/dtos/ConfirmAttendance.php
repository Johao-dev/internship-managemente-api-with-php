<?php

namespace App\meetings\dtos;

class ConfirmAttendance {
    public bool $attended;
    public ?string $comments = null;
}