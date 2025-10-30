<?php

namespace App\meetings\dtos;

class CreateMeeting {
    public string $title;
    public ?string $description = null;
    public string $startDatetime;
    public int $estimatedDuration;
    public string $type;
    public int $organizerId;
}