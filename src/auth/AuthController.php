<?php

namespace App\auth;

use App\users\UserValidator;

class AuthController {

    private AuthService $authService;

    public function __construct() {
        $this->authService = new AuthService();
    }

    public function login() {
        $data = (array) json_decode(file_get_contents('php://input'), true);
        $loginDto = AuthValidator::validateLogin($data);
        $authResponse = $this->authService->login($loginDto);
        
        return [
            'success' => true,
            'message' => 'Login exitoso.',
            'data' => $authResponse
        ];
    }

    public function register() {
        $data = (array) json_decode(file_get_contents('php://input'), true);
        $registerDto = UserValidator::validateCreate($data);
        $registerResponse = $this->authService->register($registerDto);

        http_response_code(201);
        return [
            'success' => true,
            'message' => $registerResponse->message,
            'data' => $registerResponse->user
        ];
    }
}