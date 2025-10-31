<?php

namespace App\documents;

use App\core\ApiException;
use App\core\AuthenticatedUserHandler;
use App\users\UserRole;

class DocumentRouter {

    private DocumentController $controller;

    public function __construct() {
        $this->controller = new DocumentController();
    }

    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
        $userId = isset($_GET['userId']) ? (int)$_GET['userId'] : null;
        $internId = isset($_GET['internId']) ? (int)$_GET['internId'] : null;
        $documentId = isset($_GET['documentId']) ? (int)$_GET['documentId'] : null;
        $operation = $_GET['op'] ?? null;

        $allRoles = [UserRole::ADMIN->value, UserRole::SUPERVISOR->value, UserRole::INTERN->value];

        // Route: POST api/documents?op=upload
        if ($method === 'POST' && $operation === 'upload') {
            $this->authorize($allRoles);
            return $this->controller->uploadDocument();
        }

        // Route: GET api/documents?id={id}&op=download
        if ($method === 'GET' && $id && $operation === 'download') {
            $this->authorize($allRoles);
            
            // Este método NO devuelve JSON. Envía el archivo y termina la ejecución.
            $this->controller->downloadDocument($id);
            exit; // Detener el script después de enviar el archivo
        }

        // Route: GET api/documents?id={id}
        if ($method === 'GET' && $id) {
            $this->authorize($allRoles);
            return $this->controller->findDocumentById($id);
        }

        // Route: GET api/documents?op=user&userId={userId}
        if ($method === 'GET' && $operation === 'user' && $userId) {
            $this->authorize($allRoles);
            return $this->controller->findDocumentsByUserId($userId);
        }

        // Route: GET api/documents?op=intern&internId={internId}
        if ($method === 'GET' && $operation === 'intern' && $internId) {
            $this->authorize($allRoles);
            return $this->controller->findDocumentsByInternId($internId);
        }

        // Route: POST api/documents?op=link-to-intern
        if ($method === 'POST' && $operation === 'link-to-intern') {
            $this->authorize($allRoles);
            return $this->controller->linkToIntern();
        }

        // Route: DELETE api/documents?op=unlink&documentId={docId}&internId={intId}
        if ($method === 'DELETE' && $operation === 'unlink' && $documentId && $internId) {
            $this->authorize($allRoles);
            return $this->controller->unlinkFromIntern($documentId, $internId);
        }

        throw ApiException::notFound('Operación no válida en el recurso "documents".');
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