<?php

namespace App\messaging;

use App\core\StoredProcedureExecutor;
use App\config\Database;
use PDO;

class MessageRepository {

    private StoredProcedureExecutor $executor;
    private PDO $db;


    public function __construct() {
        $this->executor = StoredProcedureExecutor::getInstance();
        $this->db = Database::getInstance()->getConnection();
    }

    public function createAndGetId(MessageEntity $message): int {
        $sql = "CALL sp_create_message(:title, :content, :remitent_id, :recipient_type)";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(":title", $message->title, PDO::PARAM_STR);
            $stmt->bindValue(":content", $message->content, PDO::PARAM_STR);
            $stmt->bindValue(":remitent_id", $message->remitent_id, PDO::PARAM_INT);
            $stmt->bindValue(":recipient_type", $message->recipient_type, PDO::PARAM_STR);
            
            $stmt->execute();
            $stmt->closeCursor();

            return (int)$this->db->lastInsertId();

        } catch (\PDOException $e) {
            error_log("MessageRepository::createAndGetId error: " . $e->getMessage());
            return 0;
        }
    }

    public function create(MessageEntity $message): bool {
        return $this->executor->execute(
            "CALL sp_create_message(:title, :content, :remitent_id, :recipient_type)",
            [
                'title'          => $message->title,
                'content'        => $message->content,
                'remitent_id'    => $message->remitent_id,
                'recipient_type' => $message->recipient_type
            ],
            false,
            null,
            true
        );
    }

    public function findById(int $id) {
        return $this->executor->execute(
            "CALL sp_get_by_id_message(:id)",
            ['id' => $id],
            false,
            MessageEntity::class
        );
    }

    public function findAll(): array {
        return $this->executor->execute(
            "CALL sp_get_all_messages()",
            [],
            true,
            MessageEntity::class
        ) ?? [];
    }

    public function findByRemitentId(int $remitentId): array {
        return $this->executor->execute(
            "CALL sp_get_by_remitent_id_messages(:remitent_id)",
            ['remitent_id' => $remitentId],
            true,
            MessageEntity::class
        ) ?? [];
    }

    public function findInboxByUserId(int $userId): array {
        return $this->executor->execute(
            "CALL sp_get_inbox_by_user_id_messages(:user_id)",
            ['user_id' => $userId],
            true,
            MessageEntity::class
        ) ?? [];
    }

    public function findUnreadByUserId(int $userId): array {
        return $this->executor->execute(
            "CALL sp_get_unread_messages_by_user_id_messages(:user_id)",
            ['user_id' => $userId],
            true,
            MessageEntity::class
        ) ?? [];
    }

    public function update(MessageEntity $message): bool {
        return $this->executor->execute(
            "CALL sp_update_message(:id, :title, :content, :remitent_id, :recipient_type, :active)",
            [
                'id'             => $message->id,
                'title'          => $message->title,
                'content'        => $message->content,
                'remitent_id'    => $message->remitent_id,
                'recipient_type' => $message->recipient_type,
                'active'         => $message->active
            ],
            false,
            null,
            true
        );
    }

    public function delete(int $id): bool {
        return $this->executor->execute(
            "CALL sp_delete_message(:id)",
            ['id' => $id],
            false,
            null,
            true
        );
    }
}