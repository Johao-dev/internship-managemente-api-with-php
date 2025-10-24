<?php

namespace App\activity_reports;

enum RevisionState: String {
    case PENDING = "Pending";
    case REVIEWED = "Reviewed";
    case REVISION_REQUIRED = "revision_required";
}

class ActivityReportEntity {

    private $id;
    private $intern_id;
    private $supervisor_id;
    private $title;
    private $content;
    private $send_date;
    private $revision_date;
    private $revision_state;
    private $supervisor_comment;
    private $active;
    private $created_at;
    private $updated_at;
}