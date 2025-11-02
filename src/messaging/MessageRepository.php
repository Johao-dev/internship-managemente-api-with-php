<?php

namespace App\messaging;

use App\core\StoredProcedureExecutor;

class MessageRepository {

    private StoredProcedureExecutor $executor;


    public function __construct() {
        $this->executor = StoredProcedureExecutor::getInstance();
    }

    public function create(MessageEntity $message): int|bool {
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