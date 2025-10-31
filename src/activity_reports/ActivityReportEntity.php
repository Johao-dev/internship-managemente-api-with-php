<?php

namespace App\activity_reports;

class ActivityReportEntity {

    public $id;
    public $intern_id;
    public $supervisor_id;
    public $title;
    public $content;
    public $send_date;
    public $revision_date;
    public $revision_state;
    public $supervisor_comment;
    public $active;
    public $created_at;
    public $updated_at;
}