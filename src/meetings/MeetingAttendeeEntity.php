<?php

namespace App\meetings;

class MeetingAttendeeEntity {

    public $id;
    public $meeting_id;
    public $user_id;
    public $attended;
    public $comments;
}