<?php

namespace App\activity_reports\dtos;

use App\interns\dtos\InternResponse;
use App\users\dtos\SupervisorResponse;

class ReportResponse {
    public int $id;
    public string $title;
    public string $content;
    public string $send_date;
    public ?string $revision_date = null;
    public string $revision_state;
    public ?string $supervisor_comment = null;
    public bool $active;
    public InternResponse $intern;
    public ?SupervisorResponse $supervisor = null;
}