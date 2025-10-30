<?php

namespace App\auth;

use App\core\ApiException;
use App\auth\dtos\Login;

class AuthValidator {

    public static function validateLogin(array $data): Login {
        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw ApiException::badRequest('El campo email es requerido y debe ser válido.');
        }
        if (empty($data['password'])) {
            throw ApiException::badRequest('El campo password es requerido.');
        }

        $dto = new Login();
        $dto->email = $data['email'];
        $dto->password = $data['password'];
        return $dto;
    }
}