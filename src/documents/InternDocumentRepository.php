<?php

namespace App\documents;

use App\core\StoredProcedureExecutor;

class InternDocumentRepository {

    private StoredProcedureExecutor $executor;

    public function __construct() {
        $this->executor = StoredProcedureExecutor::getInstance();
    }

    public function create(InternDocumentEntity $internDocument): int|bool {
        return $this->executor->execute(
            "CALL sp_create_intern_document(:document_id, :intern_id, :relation_type)",
            [
                'document_id'   => $internDocument->document_id,
                'intern_id'     => $internDocument->intern_id,
                'relation_type' => $internDocument->relation_type
            ],
            false,
            null,
            true
        );
    }

    public function findById(int $id) {
        return $this->executor->execute(
            "CALL sp_get_by_id_intern_document(:id)",
            ['id' => $id],
            false,
            InternDocumentEntity::class
        );
    }

    public function findByDocumentAndInternId(int $documentId, int $internId) {
        return $this->executor->execute(
            "CALL sp_get_by_document_and_intern_id_intern_document(:document_id, :intern_id)",
            [
                'document_id' => $documentId,
                'intern_id'   => $internId
            ],
            false,
            InternDocumentEntity::class
        );
    }

    public function findAll(): array {
        return $this->executor->execute(
            "CALL sp_get_all_intern_documents()",
            [],
            true,
            InternDocumentEntity::class
        ) ?? [];
    }

    public function findAllByDocumentId(int $documentId): array {
        $sql = "CALL sp_get_all_by_document_id_links(:document_id)";
        
        return $this->executor->execute(
            $sql,
            ['document_id' => $documentId],
            true,
            InternDocumentEntity::class
        ) ?? [];
    }

    public function update(InternDocumentEntity $internDocument): bool {
        return $this->executor->execute(
            "CALL sp_update_intern_document(:id, :document_id, :intern_id, :relation_type)",
            [
                'id'            => $internDocument->id,
                'document_id'   => $internDocument->document_id,
                'intern_id'     => $internDocument->intern_id,
                'relation_type' => $internDocument->relation_type
            ],
            false,
            null,
            true
        );
    }

    public function delete(int $id): bool {
        return $this->executor->execute(
            "CALL sp_delete_intern_document(:id)",
            ['id' => $id],
            false,
            null,
            true
        );
    }

    public function deleteByDocumentAndInternId(int $documentId, int $internId): bool {
        return $this->executor->execute(
            "CALL sp_delete_by_document_and_intern_id_intern_document(:document_id, :intern_id)",
            [
                'document_id' => $documentId,
                'intern_id'   => $internId
            ],
            false,
            null,
            true
        );
    }
}