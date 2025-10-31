<?php

namespace App\activity_reports;

enum RevisionState: String {
    case PENDING = "Pending";
    case REVIEWED = "Reviewed";
    case REVISION_REQUIRED = "revision_required";
}
