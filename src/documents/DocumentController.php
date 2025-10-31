<?php

namespace App\documents;

use App\core\ApiException;
use App\core\AuthenticatedUserHandler;
use App\documents\dtos\DocumentResponse;
use App\interns\InternService;
use App\users\UserRole;

class DocumentController {

    private DocumentService $documentService;
    private InternService $internService;
    private DocumentValidator $validator;

    public function __construct() {
        $this->documentService = new DocumentService();
        $this->internService = new InternService();
        $this->validator = new DocumentValidator();
    }

    public function uploadDocument() {
        $currentUser = AuthenticatedUserHandler::getUser();
        
        if (empty($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            throw ApiException::badRequest("No se proporcionó ningún archivo o hubo un error en la subida.");
        }

        $file = $_FILES['file'];
        $dto = $this->validator->validateUpload($_POST); 
        if ($dto->upById !== $currentUser->id) {
            throw ApiException::forbidden("No estás autorizado para subir un documento a nombre de este usuario.");
        }

        $uploadedDocument = $this->documentService->uploadDocument($file, $dto);

        http_response_code(201);
        return [
            'success' => true,
            'message' => $uploadedDocument->message,
            'data' => $uploadedDocument
        ];
    }

    public function findDocumentById(int $id) {
        $currentUser = AuthenticatedUserHandler::getUser();
        $document = $this->documentService->findDocumentById($id);

        if ($this->canUserAccessDocument($document, $currentUser)) {
            return [
                'success' => true,
                'message' => 'Documento encontrado.',
                'data' => $document
            ];
        }
        
        throw ApiException::forbidden("No estás autorizado para ver este documento.");
    }

    public function findDocumentsByUserId(int $userId) {
        $currentUser = AuthenticatedUserHandler::getUser();

        if ($currentUser->role !== UserRole::ADMIN->value && $currentUser->id !== $userId) {
            throw ApiException::forbidden("No estás autorizado para ver los documentos de este usuario.");
        }

        $documents = $this->documentService->findDocumentsUploadedByUserId($userId);
        return [
            'success' => true,
            'message' => 'Documentos encontrados.',
            'data' => $documents
        ];
    }

    public function findDocumentsByInternId(int $internId) {
        $currentUser = AuthenticatedUserHandler::getUser();
        
        if ($currentUser->role === UserRole::ADMIN->value) {
            $documents = $this->documentService->findDocumentsByInternId($internId);
            return ['success' => true, 'message' => 'Documentos encontrados.', 'data' => $documents];
        }

        $internProfile = $this->internService->findInternById($internId);
        $isSupervisor = $internProfile->supervisor && $internProfile->supervisor->user->id === $currentUser->id;
        $isOwner = $internProfile->user->id === $currentUser->id;

        if (!$isSupervisor && !$isOwner) {
            throw ApiException::forbidden("No estás autorizado para ver los documentos de este practicante.");
        }

        $documents = $this->documentService->findDocumentsByInternId($internId);
        return [
            'success' => true,
            'message' => 'Documentos encontrados.',
            'data' => $documents
        ];
    }

    public function linkToIntern() {
        $currentUser = AuthenticatedUserHandler::getUser();
        $data = (array) json_decode(file_get_contents('php://input'), true);
        $dto = $this->validator->validateLink($data);

        $document = $this->documentService->findDocumentById($dto->documentId);
        
        $isAdmin = $currentUser->role === UserRole::ADMIN->value;
        $isUploader = $currentUser->id === $document->upById;
        
        $internProfile = $this->internService->findInternById($dto->internId);
        $isSupervisor = $internProfile->supervisor && $internProfile->supervisor->user->id === $currentUser->id;

        if (!$isAdmin && !$isUploader && !$isSupervisor) {
            throw ApiException::forbidden("No estás autorizado para vincular este documento.");
        }
        
        $linkedDoc = $this->documentService->linkDocumentToIntern($dto);
        return [
            'success' => true,
            'message' => $linkedDoc->message,
            'data' => $linkedDoc
        ];
    }

    public function unlinkFromIntern(int $documentId, int $internId) {
        $currentUser = AuthenticatedUserHandler::getUser();

        $document = $this->documentService->findDocumentById($documentId);
        
        $isAdmin = $currentUser->role === UserRole::ADMIN->value;
        $isUploader = $currentUser->id === $document->upById;
        
        $internProfile = $this->internService->findInternById($internId);
        $isSupervisor = $internProfile->supervisor && $internProfile->supervisor->user->id === $currentUser->id;

        if (!$isAdmin && !$isUploader && !$isSupervisor) {
            throw ApiException::forbidden("No estás autorizado para desvincular este documento.");
        }

        $unlinkedDoc = $this->documentService->unlinkDocumentFromIntern($documentId, $internId);
        return [
            'success' => true,
            'message' => $unlinkedDoc->message,
            'data' => $unlinkedDoc
        ];
    }

    public function downloadDocument(int $id) {
        $currentUser = AuthenticatedUserHandler::getUser();
        $document = $this->documentService->findDocumentById($id);

        if (!$this->canUserAccessDocument($document, $currentUser)) {
            throw ApiException::forbidden("No estás autorizado para descargar este documento.");
        }
        
        $fileInfo = $this->documentService->getDocumentFile($id);

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($fileInfo['originalName']) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($fileInfo['path']));
        
        ob_clean();
        flush();
        readfile($fileInfo['path']);
    }

    private function canUserAccessDocument(DocumentResponse $document, $currentUser): bool {
        if ($currentUser->role === UserRole::ADMIN->value) {
            return true;
        }
        if ($currentUser->id === $document->upById) {
            return true;
        }
        
        foreach ($document->internLinks as $link) {
            if ($link->intern->user->id === $currentUser->id) {
                return true;
            }
            if ($link->intern->supervisor && $link->intern->supervisor->user->id === $currentUser->id) {
                return true;
            }
        }
        
        return false;
    }
}