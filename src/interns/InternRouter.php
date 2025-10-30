<?php

namespace App\interns;

use App\core\ApiException;
use App\core\AuthenticatedUserHandler;
use App\users\UserRole;

class InternRouter {

    private InternController $controller;

    public function __construct() {
        $this->controller = new InternController();
    }

    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
        $operation = $_GET['op'] ?? null;
        $userId = isset($_GET['userId']) ? (int)$_GET['userId'] : null;

        // Route: POST api/interns
        if ($method === 'POST' && !$id && !$operation) {
            $this->authorize([UserRole::ADMIN->value, UserRole::SUPERVISOR->value]);
            return $this->controller->createIntern();
        }

        // Route: GET api/interns?id={id}
        if ($method === 'GET' && $id) {
            $this->authorize([UserRole::ADMIN->value, UserRole::SUPERVISOR->value, UserRole::INTERN->value]);
            return $this->controller->findInternById($id);
        }

        // Route: GET api/interns?op=user&userId={userId}
        if ($method === 'GET' && $operation === 'user' && $userId) {
            $this->authorize([UserRole::ADMIN->value, UserRole::SUPERVISOR->value, UserRole::INTERN->value]);
            return $this->controller->findInternByUserId($userId);
        }

        // Route: GET api/interns
        if ($method === 'GET' && !$id && !$operation) {
            $this->authorize([UserRole::ADMIN->value, UserRole::SUPERVISOR->value]);
            return $this->controller->findAllActiveInterns();
        }

        // Route: PUT api/interns?id={id}
        if ($method === 'PUT' && $id) {
            $this->authorize([UserRole::ADMIN->value, UserRole::SUPERVISOR->value, UserRole::INTERN->value]);
            return $this->controller->updateIntern($id);
        }

        // Route: POST api/interns?id={id}&op=assign-supervisor
        if ($method === 'POST' && $id && $operation === 'assign-supervisor') {
            $this->authorize([UserRole::ADMIN->value, UserRole::SUPERVISOR->value]);
            return $this->controller->assignSupervisor($id);
        }

        // Route: PATCH api/interns?id={id}&op=deactivate
        if ($method === 'PATCH' && $id && $operation === 'deactivate') {
            $this->authorize([UserRole::ADMIN->value, UserRole::SUPERVISOR->value, UserRole::INTERN->value]);
            return $this->controller->deactivateIntern($id);
        }

        // Route: PATCH api/interns?id={id}&op=activate
        if ($method === 'PATCH' && $id && $operation === 'activate') {
            $this->authorize([UserRole::ADMIN->value, UserRole::SUPERVISOR->value]);
            return $this->controller->activateIntern($id);
        }

        throw ApiException::notFound('Operación no válida en el recurso "interns".');
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