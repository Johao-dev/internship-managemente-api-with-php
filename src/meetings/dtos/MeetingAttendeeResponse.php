<?php

namespace App\meetings\dtos;

use App\users\dtos\UserResponse;

class MeetingAttendeeResponse {
    public int $id;
    public int $meetingId;
    public bool $attended;
    public ?string $comments = null;
    public UserResponse $user;
}