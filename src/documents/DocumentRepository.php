<?php

namespace App\documents;

use App\core\StoredProcedureExecutor;

class DocumentRepository {

    private StoredProcedureExecutor $executor;

    public function __construct() {
        $this->executor = StoredProcedureExecutor::getInstance();
    }

    public function create(DocumentEntity $document): int|bool {
        return $this->executor->execute(
            "CALL sp_create_document(:original_name, :path, :document_type, :description, :up_by_id)",
            [
                'original_name' => $document->original_name,
                'path'          => $document->path,
                'document_type' => $document->document_type,
                'description'   => $document->description,
                'up_by_id'      => $document->up_by_id
            ],
            false,
            null,
            true
        );
    }

    public function findById(int $id) {
        return $this->executor->execute(
            "CALL sp_get_by_id_document(:id)",
            ['id' => $id],
            false,
            DocumentEntity::class
        );
    }

    public function findAll(): array {
        return $this->executor->execute(
            "CALL sp_get_all_documents()",
            [],
            true,
            DocumentEntity::class
        ) ?? [];
    }

    public function findAllByInternId(int $internId): array {
        return $this->executor->execute(
            "CALL sp_get_all_by_intern_id_documents(:intern_id)",
            ['intern_id' => $internId],
            true,
            DocumentEntity::class
        ) ?? [];
    }

    public function findAllByUserId(int $userId): array {
        return $this->executor->execute(
            "CALL sp_get_all_by_user_id_documents(:user_id)",
            ['user_id' => $userId],
            true,
            DocumentEntity::class
        ) ?? [];
    }

    public function update(DocumentEntity $document): bool {
        return $this->executor->execute(
            "CALL sp_update_document(:id, :original_name, :path, :document_type, :description, :up_by_id, :active)",
            [
                'id'            => $document->id,
                'original_name' => $document->original_name,
                'path'          => $document->path,
                'document_type' => $document->document_type,
                'description'   => $document->description,
                'up_by_id'      => $document->up_by_id,
                'active'        => $document->active
            ],
            false,
            null,
            true
        );
    }

    public function delete(int $id): bool {
        return $this->executor->execute(
            "CALL sp_delete_document(:id)",
            ['id' => $id],
            false,
            null,
            true
        );
    }
}