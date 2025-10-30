<?php

namespace App\users;

use App\core\ApiException;
use App\core\AuthenticatedUserHandler;

class UserRouter {

    private UserController $controller;

    public function __construct() {
        $this->controller = new UserController();
    }

    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
        $operation = $_GET['op'] ?? null;
        $email = $_GET['email'] ?? null;

        // Route: GET api/users?id={id}
        if ($method === 'GET' && $id) {
            $this->authorize([UserRole::ADMIN->value, UserRole::SUPERVISOR->value, UserRole::INTERN->value]);
            return $this->controller->getUserById($id);
        }

        // Route: GET api/users?op=email&email={email}
        if ($method === 'GET' && $operation === 'email' && $email) {
            $this->authorize([UserRole::ADMIN->value, UserRole::SUPERVISOR->value, UserRole::INTERN->value]);
            return $this->controller->getUserByEmail($email);
        }

        // Route: GET api/users
        if ($method === 'GET' && !$id && !$operation) {
            $this->authorize([UserRole::ADMIN->value]);
            return $this->controller->getAllUsers();
        }

        // Route: PUT api/users?id={id}
        if ($method === 'PUT' && $id) {
            $this->authorize([UserRole::ADMIN->value, UserRole::SUPERVISOR->value, UserRole::INTERN->value]);
            return $this->controller->updateUser($id);
        }
        
        // Route: PATCH api/users?id={id}&op=deactivate
        if ($method === 'PATCH' && $id && $operation === 'deactivate') {
            $this->authorize([UserRole::ADMIN->value, UserRole::SUPERVISOR->value, UserRole::INTERN->value]);
            return $this->controller->deactivateUser($id);
        }

        // Route: POST api/users?op=assign-supervisor-role
        if ($method === 'POST' && $operation === 'assign-supervisor-role') {
            $this->authorize([UserRole::ADMIN->value]);
            return $this->controller->assignSupervisorRole();
        }

        // Route: POST api/users
        if ($method === 'POST' && !$operation) {
            $this->authorize([UserRole::ADMIN->value]);
            return $this->controller->createUser();
        }

        throw ApiException::notFound('Operación no válida en el recurso "users".');
    }

    private function authorize(array $requiredRoles): void {
        $user = AuthenticatedUserHandler::getUser();

        if (!$user) {
            throw ApiException::unauthorized("Acceso denegado. Se requiere autenticación.");
        }
        
        if (!in_array($user->role, $requiredRoles)) {
            throw ApiException::forbidden("No tienes permiso para realizar esta acción.");
        }
    }
}