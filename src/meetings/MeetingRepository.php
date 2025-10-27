<?php

namespace App\meetings;

use App\core\StoredProcedureExecutor;

class MeetingRepository {

    private StoredProcedureExecutor $executor;

    public function __construct() {
        $this->executor = StoredProcedureExecutor::getInstance();
    }

    public function create(MeetingEntity $meeting): bool {
        return $this->executor->execute(
            "CALL sp_create_meeting(:title, :description, :start_datetime, :estimated_duration, :type, :organizer_id)",
            [
                'title'              => $meeting->title,
                'description'        => $meeting->description,
                'start_datetime'     => $meeting->start_datetime,
                'estimated_duration' => $meeting->estimated_duration,
                'type'               => $meeting->type,
                'organizer_id'       => $meeting->organizer_id
            ],
            false,
            null,
            true
        );
    }

    public function findById(int $id) {
        return $this->executor->execute(
            "CALL sp_get_by_id_meeting(:id)",
            ['id' => $id],
            false,
            MeetingEntity::class
        );
    }

    public function findByOrganizerId(int $organizerId): array {
        return $this->executor->execute(
            "CALL sp_get_by_organizer_id_meeting(:organizer_id)",
            ['organizer_id' => $organizerId],
            true,
            MeetingEntity::class
        ) ?? [];
    }

    public function findAll(): array {
        return $this->executor->execute(
            "CALL sp_get_all_meetings()",
            [],
            true,
            MeetingEntity::class
        ) ?? [];
    }

    /**
     * Devuelve todas las reuniones en las que un usuario es asistente (con datos de la reunión).
     * NOTA: Este SP devuelve un JOIN, no mapea a MeetingEntity.
     * @param int $userId El ID del usuario.
     * @return array Un array de resultados asociativos.
     */
    public function findAllForUser(int $userId): array {
        return $this->executor->execute(
            "CALL sp_get_all_for_user(:user_id)",
            ['user_id' => $userId],
            true, // fetchAll
            null  // Devuelve como array asociativo (FETCH_ASSOC)
        ) ?? [];
    }

    public function update(MeetingEntity $meeting): bool {
        return $this->executor->execute(
            "CALL sp_update_meeting(:id, :title, :description, :start_datetime, :estimated_duration, :type, :organizer_id)",
            [
                'id'                 => $meeting->id,
                'title'              => $meeting->title,
                'description'        => $meeting->description,
                'start_datetime'     => $meeting->start_datetime,
                'estimated_duration' => $meeting->estimated_duration,
                'type'               => $meeting->type,
                'organizer_id'       => $meeting->organizer_id
            ],
            false,
            null,
            true
        );
    }

    public function delete(int $id): bool {
        return $this->executor->execute(
            "CALL sp_delete_meeting(:id)",
            ['id' => $id],
            false,
            null,
            true
        );
    }
}
