<?php

namespace App\messaging;

use App\core\StoredProcedureExecutor;

class MessageRecipientRepository {

    private StoredProcedureExecutor $executor;

    public function __construct() {
        $this->executor = StoredProcedureExecutor::getInstance();
    }

    public function create(MessageRecipientEntity $recipient): bool {
        return $this->executor->execute(
            "CALL sp_create_message_recipient(:message_id, :user_id, :readed, :read_date)",
            [
                'message_id' => $recipient->message_id,
                'user_id'    => $recipient->user_id,
                'readed'     => $recipient->readed,
                'read_date'  => $recipient->read_date
            ],
            false,
            null,
            true
        );
    }

    public function findById(int $id) {
        return $this->executor->execute(
            "CALL sp_get_by_id_message_recipient(:id)",
            ['id' => $id],
            false,
            MessageRecipientEntity::class
        );
    }

    public function findByMessageAndUser(int $messageId, int $userId) {
        return $this->executor->execute(
            "CALL sp_get_by_message_id_and_user_id_message_recipient(:message_id, :user_id)",
            [
                'message_id' => $messageId,
                'user_id'    => $userId
            ],
            false,
            MessageRecipientEntity::class
        );
    }

    public function findAll(): array {
        return $this->executor->execute(
            "CALL sp_get_all_message_recipients()",
            [],
            true,
            MessageRecipientEntity::class
        ) ?? [];
    }

    public function findAllByMessageId(int $messageId) {
        return $this->executor->execute(
            "CALL sp_get_all_by_message_id_recipients(:message_id)",
            ['message_id', $messageId],
            true,
            MessageRecipientEntity::class
        ) ?? [];
    }

    public function update(MessageRecipientEntity $recipient): bool {
        return $this->executor->execute(
            "CALL sp_update_message_recipient(:id, :message_id, :user_id, :readed, :read_date)",
            [
                'id'         => $recipient->id,
                'message_id' => $recipient->message_id,
                'user_id'    => $recipient->user_id,
                'readed'     => $recipient->readed,
                'read_date'  => $recipient->read_date
            ],
            false,
            null,
            true
        );
    }

    public function markAsRead(int $messageId, int $userId): bool {
        return $this->executor->execute(
            "CALL sp_mark_message_as_read_message_recipeint(:message_id, :user_id)",
            [
                'message_id' => $messageId,
                'user_id'    => $userId
            ],
            false,
            null,
            true
        );
    }

    public function delete(int $id): bool {
        return $this->executor->execute(
            "CALL sp_delete_message_recipient(:id)",
            ['id' => $id],
            false,
            null,
            true
        );
    }
}