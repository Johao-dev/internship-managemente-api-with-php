<?php

namespace App\users;

use App\core\StoredProcedureExecutor;

class SupervisorRepository {

    private StoredProcedureExecutor $executor;

    public function __construct() {
        $this->executor = StoredProcedureExecutor::getInstance();
    }

    public function findById(int $id) {
        return $this->executor->execute(
            "CALL sp_get_by_id_supervisor(:id);",
            ['id' => $id],
            false,
            SupervisorEntity::class
        );
    }

    public function findAll() {
        return $this->executor->execute(
            "CALL sp_get_all_supervisors();",
            [],
            true,
            SupervisorEntity::class
        ) ?? [];
    }

    public function create(SupervisorEntity $supervisor) {
        return (bool) $this->executor->execute(
            "CALL sp_create_supervisor(:user_id, :area);",
            [
                'user_id' => $supervisor->user_id,
                'area'    => $supervisor->area
            ],
            false,
            null,
            true
        );
    }

    public function update(SupervisorEntity $supervisor) {
        return (bool) $this->executor->execute(
            "CALL sp_update_supervisor(:id, :user_id, :area);",
            [
                'id'      => $supervisor->id,
                'user_id' => $supervisor->user_id,
                'area'    => $supervisor->area
            ],
            false,
            null,
            true
        );
    }

    public function delete(int $id) {
        return (bool) $this->executor->execute(
            "CALL sp_delete_supervisor(:id);",
            ['id' => $id],
            false,
            null,
            true
        );
    }
}
