<?php

namespace App\meetings;

use App\core\ApiException;
use App\core\AuthenticatedUserHandler;
use App\users\UserRole;

class MeetingRouter {

    private MeetingController $controller;

    public function __construct() {
        $this->controller = new MeetingController();
    }

    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
        $organizerId = isset($_GET['organizerId']) ? (int)$_GET['organizerId'] : null;
        $userId = isset($_GET['userId']) ? (int)$_GET['userId'] : null;
        $operation = $_GET['op'] ?? null;

        $allRoles = [UserRole::ADMIN->value, UserRole::SUPERVISOR->value, UserRole::INTERN->value];

        // Route: POST api/meetings
        if ($method === 'POST' && !$id && !$operation) {
            $this->authorize($allRoles);
            return $this->controller->createMeeting();
        }

        // Route: GET api/meetings?id={id}
        if ($method === 'GET' && $id) {
            $this->authorize($allRoles);
            return $this->controller->findMeetingById($id);
        }

        // Route: GET api/meetings?op=organizer&organizerId={organizerId}
        if ($method === 'GET' && $operation === 'organizer' && $organizerId) {
            $this->authorize($allRoles);
            return $this->controller->findMeetingsByOrganizerId($organizerId);
        }

        // Route: GET api/meetings?op=user&userId={userId}
        if ($method === 'GET' && $operation === 'user' && $userId) {
            $this->authorize($allRoles);
            return $this->controller->findMeetingsForUser($userId);
        }

        // Route: PUT api/meetings?id={id}
        if ($method === 'PUT' && $id) {
            $this->authorize($allRoles);
            return $this->controller->updateMeeting($id);
        }

        // Route: POST api/meetings?id={id}&op=add-attendee
        if ($method === 'POST' && $id && $operation === 'add-attendee') {
            $this->authorize($allRoles);
            return $this->controller->addAttendee($id);
        }

        // Route: DELETE api/meetings?id={id}&op=remove-attendee&userId={userId}
        if ($method === 'DELETE' && $id && $operation === 'remove-attendee' && $userId) {
            $this->authorize($allRoles);
            return $this->controller->removeAttendee($id, $userId);
        }

        // Route: PATCH api/meetings?id={id}&op=confirm-attendance&userId={userId}
        if ($method === 'PATCH' && $id && $operation === 'confirm-attendance' && $userId) {
            $this->authorize($allRoles);
            return $this->controller->confirmAttendance($id, $userId);
        }

        throw ApiException::notFound('Operación no válida en el recurso "meetings".');
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