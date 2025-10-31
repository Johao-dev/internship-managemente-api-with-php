<?php

namespace App\messaging;

enum RecipientType: String {
    case INTERN = "intern";
    case SUPERVISOR = "supervisor";
    case ALL = "all";
}
