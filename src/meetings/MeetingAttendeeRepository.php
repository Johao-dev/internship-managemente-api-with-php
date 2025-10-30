<?php

namespace App\meetings;

use App\core\StoredProcedureExecutor;
use App\config\Database;
use PDO;

class MeetingAttendeeRepository {

    private StoredProcedureExecutor $executor;
    private PDO $db;

    public function __construct() {
        $this->executor = StoredProcedureExecutor::getInstance();
        $this->db = Database::getInstance()->getConnection();
    }

    public function createAndGetId(MeetingAttendeeEntity $attendee) {
        $sql = "CALL sp_create_meeting_attendee(:meeting_id, :user_id, :attended, :comments)";

        try {
            $stmt = $this->db->prepare($sql);

            $stmt->bindValue(":meeting_id", $attendee->meeting_id, PDO::PARAM_INT);
            $stmt->bindValue(":user_id", $attendee->user_id, PDO::PARAM_INT);
            $stmt->bindValue(":attended", $attendee->attended, PDO::PARAM_STR);
            $stmt->bindValue(":comments", $attendee->comments, PDO::PARAM_STR);

            $stmt->execute();
            $stmt->closeCursor();

            return (int)$this->db->lastInsertId();

        } catch (\PDOException $e) {
            error_log("MeetingAttendeeRepository::createAndGetId error: " . $e->getMessage());
            return 0;
        }
    }

    public function create(MeetingAttendeeEntity $attendee): bool {
        return $this->executor->execute(
            "CALL sp_create_meeting_attendee(:meeting_id, :user_id, :attended, :comments)",
            [
                'meeting_id' => $attendee->meeting_id,
                'user_id'    => $attendee->user_id,
                'attended'   => $attendee->attended,
                'comments'   => $attendee->comments
            ],
            false,
            null,
            true
        );
    }

    public function findById(int $id) {
        return $this->executor->execute(
            "CALL sp_get_by_id_meeting_attendee(:id)",
            ['id' => $id],
            false,
            MeetingAttendeeEntity::class
        );
    }

    public function findByMeetingAndUser(int $meetingId, int $userId) {
        return $this->executor->execute(
            "CALL sp_get_by_meeting_id_and_user_id_meeting_attendee(:meeting_id, :user_id)",
            [
                'meeting_id' => $meetingId,
                'user_id'    => $userId
            ],
            false,
            MeetingAttendeeEntity::class
        );
    }

    public function findAll(): array {
        return $this->executor->execute(
            "CALL sp_get_all_meeting_attendees()",
            [],
            true,
            MeetingAttendeeEntity::class
        ) ?? [];
    }

    public function findAllByMeetingId(int $meetingId): array {
        return $this->executor->execute(
            "CALL sp_get_all_by_meeting_id_meeting_attendees(:meeting_id)",
            ['meeting_id' => $meetingId],
            true,
            MeetingAttendeeEntity::class
        ) ?? [];
    }

    public function findAllByUserId(int $userId): array {
        return $this->executor->execute(
            "CALL sp_get_all_by_user_id_meeting_attendees(:user_id)",
            ['meeting_id', $userId],
            true,
            MeetingAttendeeEntity::class
        );
    }

    public function update(MeetingAttendeeEntity $attendee): bool {
        return $this->executor->execute(
            "CALL sp_update_meeting_attendee(:id, :meeting_id, :user_id, :attended, :comments)",
            [
                'id'         => $attendee->id,
                'meeting_id' => $attendee->meeting_id,
                'user_id'    => $attendee->user_id,
                'attended'   => $attendee->attended,
                'comments'   => $attendee->comments
            ],
            false,
            null,
            true
        );
    }

    public function delete(int $id): bool {
        return $this->executor->execute(
            "CALL sp_delete_meeting_attendee(:id)",
            ['id' => $id],
            false,
            null,
            true
        );
    }
}