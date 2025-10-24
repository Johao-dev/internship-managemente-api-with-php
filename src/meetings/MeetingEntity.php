<?php

namespace App\meetings;

enum MeetingType: String {
    case PRESENTIAL = "presential";
    case VIRTUAL = "virtual";
}

class MeetingEntity {

    private $id;
    private $title;
    private $description;
    private $start_datetime;
    private $estimated_duration;
    private $type;
    private $organizer_id;
    private $created_at;
}