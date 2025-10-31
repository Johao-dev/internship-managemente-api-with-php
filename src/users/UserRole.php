<?php

namespace App\users;

enum UserRole: String {
    case ADMIN = "ADMIN";
    case SUPERVISOR = "SUPERVISOR";
    case INTERN = "INTERN";
}
