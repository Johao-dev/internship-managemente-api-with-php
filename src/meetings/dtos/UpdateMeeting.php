<?php

namespace App\meetings\dtos;

class UpdateMeeting {
    public ?string $title = null;
    public ?string $description = null;
    public ?string $startDatetime = null;
    public ?int $estimatedDuration = null;
    public ?string $type = null;
}
