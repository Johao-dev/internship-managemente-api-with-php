<?php

namespace App\auth;

use App\core\ApiException;

class AuthRouter {

    private AuthController $controller;

    public function __construct() {
        $this->controller = new AuthController();
    }

    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        $operation = $_GET['op'] ?? null;

        if ($method === 'POST') {
            switch ($operation) {
                case 'login':
                    return $this->controller->login();
                case 'register':
                    return $this->controller->register();
            }
        }

        throw ApiException::notFound('Operación de autenticación no válida.');
    }
}