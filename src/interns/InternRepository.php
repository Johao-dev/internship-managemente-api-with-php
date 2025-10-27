<?php

namespace App\interns;

use App\core\StoredProcedureExecutor;

class InternRepository {

    private StoredProcedureExecutor $executor;

    public function __construct() {
        $this->executor = StoredProcedureExecutor::getInstance();
    }

    public function create(InternEntity $intern): bool {
        return $this->executor->execute(
            "CALL sp_create_intern(:user_id, :university, :career, :internship_start_date, :internship_end_date, :supervisor_id)",
            [
                'user_id'               => $intern->user_id,
                'university'            => $intern->university,
                'career'                => $intern->career,
                'internship_start_date' => $intern->internship_start_date,
                'internship_end_date'   => $intern->internship_end_date,
                'supervisor_id'         => $intern->supervisor_id
            ],
            false,
            null,
            true
        );
    }

    public function findById(int $id) {
        return $this->executor->execute(
            "CALL sp_get_by_id_intern(:id)",
            ['id' => $id],
            false,
            InternEntity::class
        );
    }

    public function findByUserId(int $userId) {
        return $this->executor->execute(
            "CALL sp_get_by_user_id_intern(:user_id)",
            ['user_id' => $userId],
            false,
            InternEntity::class
        );
    }

    public function findAll(): array {
        return $this->executor->execute(
            "CALL sp_get_all_interns()",
            [],
            true,
            InternEntity::class
        ) ?? [];
    }

    public function findAllBySupervisorId(int $supervisorId): array {
        return $this->executor->execute(
            "CALL sp_get_all_by_supervisor_id_interns(:supervisor_id)",
            ['supervisor_id' => $supervisorId],
            true,
            InternEntity::class
        ) ?? [];
    }

    public function findAllActive(): array {
        return $this->executor->execute(
            "CALL sp_get_all_active_interns()",
            [],
            true,
            InternEntity::class
        ) ?? [];
    }

    public function update(InternEntity $intern): bool {
        return $this->executor->execute(
            "CALL sp_update_intern(:id, :user_id, :university, :career, :internship_start_date, :internship_end_date, :supervisor_id, :active)",
            [
                'id'                    => $intern->id,
                'user_id'               => $intern->user_id,
                'university'            => $intern->university,
                'career'                => $intern->career,
                'internship_start_date' => $intern->internship_start_date,
                'internship_end_date'   => $intern->internship_end_date,
                'supervisor_id'         => $intern->supervisor_id,
                'active'                => $intern->active
            ],
            false,
            null,
            true
        );
    }

    public function delete(int $id): bool {
        return $this->executor->execute(
            "CALL sp_delete_intern(:id)",
            ['id' => $id],
            false,
            null,
            true
        );
    }
}