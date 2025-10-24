<?php

namespace App\users;

enum UserRole: String {
    case ADMIN = "ADMIN";
    case SUPERVISOR = "SUPERVISOR";
    case INTERN = "INTERN";
}

class UserEntity {

    private $id;
    private $full_name;
    private $institutional_email;
    private $role;
    private $password;
    private $created_at;
    private $updated_at;
    private $active;
}