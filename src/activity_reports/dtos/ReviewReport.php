<?php

namespace App\activity_reports\dtos;

class ReviewReport {
    public int $supervisorId;
    public string $revisionState;
    public ?string $supervisorComment = null;
}