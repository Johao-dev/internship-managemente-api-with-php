<?php

namespace App\activity_reports;

use App\core\ApiException;
use App\activity_reports\dtos\ReviewReport;
use App\activity_reports\dtos\SubmitReport;
use App\activity_reports\dtos\UpdateReport;

class ActivityReportValidator {

    public static function validateSubmit(array $data): SubmitReport {
        if (empty($data['internId']) || !is_numeric($data['internId'])) {
            throw ApiException::badRequest('El campo internId es requerido y debe ser un número.');
        }
        if (empty($data['title']) || !is_string($data['title'])) {
            throw ApiException::badRequest('El campo title es requerido.');
        }
        if (empty($data['content']) || !is_string($data['content'])) {
            throw ApiException::badRequest('El campo content es requerido.');
        }

        $dto = new SubmitReport();
        $dto->internId = (int)$data['internId'];
        $dto->title = $data['title'];
        $dto->content = $data['content'];
        return $dto;
    }

    public static function validateReview(array $data): ReviewReport {
        if (empty($data['supervisorId']) || !is_numeric($data['supervisorId'])) {
            throw ApiException::badRequest('El campo supervisorId es requerido y debe ser un número.');
        }
        if (empty($data['revisionState']) || !RevisionState::tryFrom($data['revisionState'])) {
            throw ApiException::badRequest('El campo revisionState es requerido y debe ser (Pending, Reviewed, revision_required).');
        }

        $dto = new ReviewReport();
        $dto->supervisorId = (int)$data['supervisorId'];
        $dto->revisionState = $data['revisionState'];

        if (isset($data['supervisorComment'])) {
            if (!is_string($data['supervisorComment'])) {
                throw ApiException::badRequest('El campo supervisorComment debe ser un string.');
            }
            $dto->supervisorComment = $data['supervisorComment'];
        }

        return $dto;
    }

    public static function validateUpdate(array $data): UpdateReport {
        $dto = new UpdateReport();

        if (isset($data['content'])) {
            if (!is_string($data['content'])) {
                throw ApiException::badRequest('El campo content debe ser un string.');
            }
            $dto->content = $data['content'];
        }

        return $dto;
    }
}