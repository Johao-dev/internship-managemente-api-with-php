<?php

namespace App\documents;

use App\core\StoredProcedureExecutor;
use App\config\Database;
use PDO;

class InternDocumentRepository {

    private StoredProcedureExecutor $executor;
    private PDO $db;

    public function __construct() {
        $this->executor = StoredProcedureExecutor::getInstance();
        $this->db = Database::getInstance()->getConnection();
    }

    public function createAndGetId(InternDocumentEntity $link): int {
        $sql = "CALL sp_create_intern_document(:document_id, :intern_id, :relation_type)";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(":document_id", $link->document_id, PDO::PARAM_INT);
            $stmt->bindValue(":intern_id", $link->intern_id, PDO::PARAM_INT);
            $stmt->bindValue(":relation_type", $link->relation_type, PDO::PARAM_STR);
            
            $stmt->execute();
            $stmt->closeCursor();
            
            return (int)$this->db->lastInsertId();

        } catch (\PDOException $e) {
            error_log("InternDocumentRepository::createAndGetId error: " . $e->getMessage());
            return 0;
        }
    }

    public function create(InternDocumentEntity $internDocument): bool {
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