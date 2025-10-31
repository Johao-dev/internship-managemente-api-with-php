<?php

namespace App\meetings;

class MeetingEntity {

    public $id;
    public $title;
    public $description;
    public $start_datetime;
    public $estimated_duration;
    public $type;
    public $organizer_id;
    public $created_at;
}