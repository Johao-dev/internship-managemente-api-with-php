<?php

namespace App\messaging;

use App\core\ApiException;
use App\core\AuthenticatedUserHandler;
use App\users\UserRole;

class MessageRouter {

    private MessagingController $controller;

    public function __construct() {
        $this->controller = new MessagingController();
    }

    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
        $remitentId = isset($_GET['remitentId']) ? (int)$_GET['remitentId'] : null;
        $userId = isset($_GET['userId']) ? (int)$_GET['userId'] : null;
        $operation = $_GET['op'] ?? null;

        $allRoles = [UserRole::ADMIN->value, UserRole::SUPERVISOR->value, UserRole::INTERN->value];

        // Route: POST api/messaging
        if ($method === 'POST' && !$id && !$operation) {
            $this->authorize($allRoles);
            return $this->controller->sendMessage();
        }

        // Route: GET api/messaging?id={id}
        if ($method === 'GET' && $id) {
            $this->authorize($allRoles);
            return $this->controller->findMessageById($id);
        }

        // Route: GET api/messaging?op=remitent&remitentId={remitentId}
        if ($method === 'GET' && $operation === 'remitent' && $remitentId) {
            $this->authorize($allRoles);
            return $this->controller->findMessagesByRemitentId($remitentId);
        }

        // Route: GET api/messaging?op=inbox&userId={userId}
        if ($method === 'GET' && $operation === 'inbox' && $userId) {
            $this->authorize($allRoles);
            return $this->controller->findInboxForUser($userId);
        }

        // Route: GET api/messaging?op=unread-count&userId={userId}
        if ($method === 'GET' && $operation === 'unread-count' && $userId) {
            $this->authorize($allRoles);
            return $this->controller->getUnreadCount($userId);
        }

        // Route: PATCH api/messaging?id={id}&op=read
        if ($method === 'PATCH' && $id && $operation === 'read') {
            $this->authorize($allRoles);
            return $this->controller->markMessageAsRead($id);
        }

        throw ApiException::notFound('Operación no válida en el recurso "messaging".');
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