<?php

namespace App\users;

enum UserRole: String {
    case ADMIN = "ADMIN";
    case SUPERVISOR = "SUPERVISOR";
    case INTERN = "INTERN";
}

class UserEntity {

    public $id;
    public $full_name;
    public $institutional_email;
    public $role;
    public $password;
    public $created_at;
    public $updated_at;
    public $active;
}