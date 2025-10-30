<?php

namespace App\meetings\dtos;

use App\users\dtos\UserResponse;

class MeetingResponse {
    public int $id;
    public string $title;
    public ?string $description = null;
    public string $startDatetime;
    public int $estimatedDuration;
    public string $type;
    public UserResponse $organizer;
    
    /** @var MeetingAttendeeResponse[] */
    public array $attendees = [];
}