<?php

namespace App\core;

class AuthenticatedUserHandler {

    private static $currentUser = null;

    public static function setUser($user) {
        self::$currentUser = $user;
    }

    public static function getUser() {
        return self::$currentUser;
    }

    public static function getUserId() {
        return self::$currentUser ? self::$currentUser->id : null;
    }
}