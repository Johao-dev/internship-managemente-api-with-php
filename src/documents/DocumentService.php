<?php

namespace App\documents;

use App\core\ApiException;
use App\core\Mapper;
use App\documents\dtos\DocumentLinked;
use App\documents\dtos\DocumentResponse;
use App\documents\dtos\DocumentUnlinked;
use App\documents\dtos\InternDocumentResponse;
use App\documents\dtos\LinkDocumentToIntern;
use App\documents\dtos\UploadDocument;
use App\documents\dtos\UploadedDocument;
use App\interns\InternService;
use App\users\UserService;

class DocumentService {

    private string $uploadPath = 'uploads';

    private DocumentRepository $documentRepository;
    private InternDocumentRepository $internDocRepository;
    private UserService $userService;
    private InternService $internService;

    public function __construct() {
        $this->documentRepository = new DocumentRepository();
        $this->internDocRepository = new InternDocumentRepository();
        $this->userService = new UserService();
        $this->internService = new InternService();

        if (!file_exists($this->uploadPath)) {
            mkdir($this->uploadPath, 0777, true);
        }
    }

    public function uploadDocument(array $file, UploadDocument $uploadDto): UploadedDocument {
        $this->userService->findUserById($uploadDto->upById);

        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw ApiException::badRequest("Error al subir el archivo. Código: " . $file['error']);
        }

        $originalName = basename($file['name']);
        $uniqueFileName = time() . '_' . preg_replace("/[^a-zA-Z0-9\._-]/", "_", $originalName);
        $filePath = $this->uploadPath . '/' . $uniqueFileName;

        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            throw ApiException::internalServerError('Falló al guardar el archivo subido.');
        }

        $newDocument = new DocumentEntity();
        $newDocument->original_name = $originalName;
        $newDocument->path = $filePath;
        $newDocument->document_type = $uploadDto->documentType;
        $newDocument->description = $uploadDto->description;
        $newDocument->up_by_id = $uploadDto->upById;
        $newDocument->up_date = date('Y-m-d H:i:s');

        $newDocId = $this->documentRepository->create($newDocument);
        if ($newDocId === 0) {
            throw ApiException::internalServerError("No se pudo guardar el registro del documento.");
        }

        $savedDocument = $this->findDocumentOrFail($newDocId);
        $response = $this->buildDocumentResponse($savedDocument);
        
        $uploadedDto = Mapper::mapToDto(UploadedDocument::class, $response);
        $uploadedDto->message = "Documento subido exitosamente.";
        return $uploadedDto;
    }

    public function findDocumentById(int $id): DocumentResponse {
        $document = $this->findDocumentOrFail($id);
        return $this->buildDocumentResponse($document);
    }

    public function findDocumentsUploadedByUserId(int $userId): array {
        $documents = $this->documentRepository->findAllByUserId($userId);
        
        $responseArray = [];
        foreach ($documents as $document) {
            $responseArray[] = $this->buildDocumentResponse($document);
        }
        return $responseArray;
    }

    public function findDocumentsByInternId(int $internId): array {
        $documents = $this->documentRepository->findAllByInternId($internId);
        
        $responseArray = [];
        foreach ($documents as $document) {
            $responseArray[] = $this->buildDocumentResponse($document);
        }
        return $responseArray;
    }

    public function linkDocumentToIntern(LinkDocumentToIntern $linkDto): DocumentLinked {
        $this->findDocumentOrFail($linkDto->documentId);
        $this->internService->findInternById($linkDto->internId);

        $existingLink = $this->internDocRepository->findByDocumentAndInternId($linkDto->documentId, $linkDto->internId);
        if ($existingLink) {
            throw ApiException::conflict("Documento con id {$linkDto->documentId} ya está vinculado al practicante {$linkDto->internId}.");
        }

        $newLink = new InternDocumentEntity();
        $newLink->document_id = $linkDto->documentId;
        $newLink->intern_id = $linkDto->internId;
        $newLink->relation_type = $linkDto->relationType;

        $newLinkId = $this->internDocRepository->create($newLink);
        if ($newLinkId === 0) {
            throw ApiException::internalServerError("No se pudo vincular el documento.");
        }

        $savedLink = $this->internDocRepository->findById($newLinkId);
        $response = $this->buildInternDocumentResponse($savedLink);

        $linkedDto = Mapper::mapToDto(DocumentLinked::class, $response);
        $linkedDto->message = "Documento vinculado al practicante exitosamente.";
        return $linkedDto;
    }

    public function unlinkDocumentFromIntern(int $documentId, int $internId): DocumentUnlinked {
        $existingLink = $this->internDocRepository->findByDocumentAndInternId($documentId, $internId);
        if (!$existingLink) {
            throw ApiException::notFound("Documento con id {$documentId} no está vinculado al practicante {$internId}.");
        }

        $this->internDocRepository->deleteByDocumentAndInternId($documentId, $internId);

        return new DocumentUnlinked(true, "Documento desvinculado.", $documentId, $internId);
    }

    public function getDocumentFile(int $id): array {
        $document = $this->findDocumentOrFail($id);
        
        if (!file_exists($document->path)) {
            throw ApiException::notFound("El archivo físico no se encuentra en el servidor.");
        }
        
        return [
            'path' => $document->path,
            'originalName' => $document->original_name
        ];
    }

    private function findDocumentOrFail(int $id): DocumentEntity {
        $document = $this->documentRepository->findById($id);
        if (!$document) {
            throw ApiException::notFound("Documento con id {$id} no encontrado.");
        }
        return $document;
    }

    private function buildDocumentResponse(DocumentEntity $doc): DocumentResponse {
        $response = Mapper::mapToDto(DocumentResponse::class, $doc);
        
        $response->originalName = $doc->original_name;
        $response->documentType = $doc->document_type;
        $response->upDate = $doc->up_date;
        $response->upById = $doc->up_by_id;
        
        $response->upBy = $this->userService->findUserById($doc->up_by_id);
        $links = $this->internDocRepository->findAllByDocumentId($doc->id);
        
        $linkDtos = [];
        foreach ($links as $link) {
            $linkDtos[] = $this->buildInternDocumentResponse($link);
        }
        $response->internLinks = $linkDtos;

        return $response;
    }

    private function buildInternDocumentResponse(InternDocumentEntity $link): InternDocumentResponse {
        $response = Mapper::mapToDto(InternDocumentResponse::class, $link);
        
        $response->documentId = $link->document_id;
        $response->internId = $link->intern_id;
        $response->relationType = $link->relation_type;
        
        $response->intern = $this->internService->findInternById($link->intern_id);

        return $response;
    }
}