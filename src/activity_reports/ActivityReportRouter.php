<?php

namespace App\activity_reports;

use App\core\ApiException;
use App\core\AuthenticatedUserHandler;
use App\users\UserRole;

class ActivityReportRouter {

    private ActivityReportController $controller;

    public function __construct() {
        $this->controller = new ActivityReportController();
    }

    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
        $internId = isset($_GET['internId']) ? (int)$_GET['internId'] : null;
        $supervisorId = isset($_GET['supervisorId']) ? (int)$_GET['supervisorId'] : null;
        $operation = $_GET['op'] ?? null;

        // Route: POST api/activity-reports?op=submit
        if ($method === 'POST' && $operation === 'submit') {
            $this->authorize([UserRole::INTERN->value]);
            return $this->controller->submitReport();
        }

        // Route: GET api/activity-reports?id={id}
        if ($method === 'GET' && $id) {
            $this->authorize([UserRole::ADMIN->value, UserRole::SUPERVISOR->value, UserRole::INTERN->value]);
            return $this->controller->findReportById($id);
        }

        // Route: GET api/activity-reports?op=intern&internId={internId}
        if ($method === 'GET' && $operation === 'intern' && $internId) {
            $this->authorize([UserRole::ADMIN->value, UserRole::SUPERVISOR->value, UserRole::INTERN->value]);
            return $this->controller->findReportsByInternId($internId);
        }

        // Route: GET api/activity-reports?op=pending-supervisor&supervisorId={supervisorId}
        if ($method === 'GET' && $operation === 'pending-supervisor' && $supervisorId) {
            $this->authorize([UserRole::SUPERVISOR->value]);
            return $this->controller->findPendingForSupervisor($supervisorId);
        }
        
        // Route: PATCH api/activity-reports?id={id}&op=review
        if ($method === 'PATCH' && $id && $operation === 'review') {
            $this->authorize([UserRole::SUPERVISOR->value]);
            return $this->controller->reviewReport($id);
        }

        // Route: PUT api/activity-reports?id={id}&op=content
        if ($method === 'PUT' && $id && $operation === 'content') {
            $this->authorize([UserRole::INTERN->value]);
            return $this->controller->updateReportContent($id);
        }

        throw ApiException::notFound('Operación no válida en el recurso "activity-reports".');
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