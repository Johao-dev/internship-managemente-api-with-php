<?php

namespace App\activity_reports;

use App\core\StoredProcedureExecutor;

class ActivityReportRepository {

    private StoredProcedureExecutor $executor;

    public function __construct() {
        $this->executor = StoredProcedureExecutor::getInstance();
    }

    public function create(ActivityReportEntity $report): bool {
        return $this->executor->execute(
            "CALL sp_create_activity_report(:intern_id, :supervisor_id, :title, :content, :send_date, :revision_date, :revision_state, :supervisor_comment)",
            [
                'intern_id'          => $report->intern_id,
                'supervisor_id'      => $report->supervisor_id,
                'title'              => $report->title,
                'content'            => $report->content,
                'send_date'          => $report->send_date,
                'revision_date'      => $report->revision_date,
                'revision_state'     => $report->revision_state,
                'supervisor_comment' => $report->supervisor_comment
            ],
            false,
            null,
            true
        );
    }

    public function findById(int $id) {
        return $this->executor->execute(
            "CALL sp_get_by_id_activity_report(:id)",
            ['id' => $id],
            false,
            ActivityReportEntity::class
        );
    }

    public function findAll(): array {
        return $this->executor->execute(
            "CALL sp_get_all_activity_reports()",
            [],
            true,
            ActivityReportEntity::class
        ) ?? [];
    }

    public function findByInternId(int $internId): array {
        return $this->executor->execute(
            "CALL sp_get_by_intern_id_activity_reports(:intern_id)",
            ['intern_id' => $internId],
            true,
            ActivityReportEntity::class
        ) ?? [];
    }

    public function findBySupervisorId(int $supervisorId): array {
        return $this->executor->execute(
            "CALL sp_get_by_supervisor_id_activity_reports(:supervisor_id)",
            ['supervisor_id' => $supervisorId],
            true,
            ActivityReportEntity::class
        ) ?? [];
    }

    public function findPendingBySupervisorId(int $supervisorId): array {
        return $this->executor->execute(
            "CALL sp_get_pending_reports_by_supervisor_id_activity_reports(:supervisor_id)",
            ['supervisor_id' => $supervisorId],
            true,
            ActivityReportEntity::class
        ) ?? [];
    }

    public function findAllPending(): array {
        return $this->executor->execute(
            "CALL sp_get_all_pending_reports_activity_reports()",
            [],
            true,
            ActivityReportEntity::class
        ) ?? [];
    }

    public function update(ActivityReportEntity $report): bool {
        return $this->executor->execute(
            "CALL sp_update_activity_report(:id, :intern_id, :supervisor_id, :title, :content, :send_date, :revision_date, :revision_state, :supervisor_comment, :active)",
            [
                'id'                 => $report->id,
                'intern_id'          => $report->intern_id,
                'supervisor_id'      => $report->supervisor_id,
                'title'              => $report->title,
                'content'            => $report->content,
                'send_date'          => $report->send_date,
                'revision_date'      => $report->revision_date,
                'revision_state'     => $report->revision_state,
                'supervisor_comment' => $report->supervisor_comment,
                'active'             => $report->active
            ],
            false,
            null,
            true
        );
    }

    public function delete(int $id): bool {
        return $this->executor->execute(
            "CALL sp_delete_activity_report(:id)",
            ['id' => $id],
            false,
            null,
            true
        );
    }
}