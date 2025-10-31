<?php

namespace App\documents;

use App\core\ApiException;
use App\documents\dtos\LinkDocumentToIntern;
use App\documents\dtos\UploadDocument;
use App\documents\DocumentType;

class DocumentValidator {

    public static function validateUpload(array $data): UploadDocument {
        if (empty($data['documentType']) || !DocumentType::tryFrom($data['documentType'])) {
            throw ApiException::badRequest('El campo documentType es requerido y debe ser (CV, certificate, report, other).');
        }
        if (empty($data['upById']) || !is_numeric($data['upById'])) {
            throw ApiException::badRequest('El campo upById es requerido.');
        }

        $dto = new UploadDocument();
        $dto->documentType = $data['documentType'];
        $dto->upById = (int)$data['upById'];

        if (isset($data['description']) && is_string($data['description'])) {
            $dto->description = $data['description'];
        }

        return $dto;
    }

    public static function validateLink(array $data): LinkDocumentToIntern {
        if (empty($data['documentId']) || !is_numeric($data['documentId'])) {
            throw ApiException::badRequest('El campo documentId es requerido.');
        }
        if (empty($data['internId']) || !is_numeric($data['internId'])) {
            throw ApiException::badRequest('El campo internId es requerido.');
        }
        if (empty($data['relationType']) || !is_string($data['relationType'])) {
            throw ApiException::badRequest('El campo relationType es requerido.');
        }

        $dto = new LinkDocumentToIntern();
        $dto->documentId = (int)$data['documentId'];
        $dto->internId = (int)$data['internId'];
        $dto->relationType = $data['relationType'];
        return $dto;
    }
}