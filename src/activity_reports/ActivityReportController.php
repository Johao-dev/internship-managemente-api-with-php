<?php

namespace App\activity_reports;

use App\core\ApiException;
use App\core\AuthenticatedUserHandler;
use App\interns\InternService;
use App\users\SupervisorRepository;
use App\users\UserRole;

class ActivityReportController {

    private ActivityReportService $reportService;
    private InternService $internService;
    private SupervisorRepository $supervisorRepository;
    private ActivityReportValidator $validator;

    public function __construct() {
        $this->reportService = new ActivityReportService();
        $this->internService = new InternService();
        $this->supervisorRepository = new SupervisorRepository();
        $this->validator = new ActivityReportValidator();
    }

    public function submitReport() {
        $currentUser = AuthenticatedUserHandler::getUser();
        $data = (array) json_decode(file_get_contents('php://input'), true);
        $dto = $this->validator->validateSubmit($data);

        $internProfile = $this->internService->findInternByUserId($currentUser->id);
        
        if ($internProfile->id !== $dto->internId) {
            throw ApiException::forbidden("No estás autorizado para enviar un reporte para este practicante.");
        }

        $newReport = $this->reportService->submitReport($dto);
        
        http_response_code(201);
        return [
            'success' => true,
            'message' => 'Reporte enviado exitosamente.',
            'data' => $newReport
        ];
    }

    public function findReportById(int $id) {
        $currentUser = AuthenticatedUserHandler::getUser();
        $report = $this->reportService->findReportById($id);

        $isAdmin = $currentUser->role === UserRole::ADMIN->value;
        $isSupervisor = $report->supervisor && $report->supervisor->user->id === $currentUser->id;
        $isOwner = $report->intern->user->id === $currentUser->id;

        if (!$isAdmin && !$isSupervisor && !$isOwner) {
            throw ApiException::forbidden("No estás autorizado para ver este reporte.");
        }

        return [
            'success' => true,
            'message' => 'Reporte encontrado.',
            'data' => $report
        ];
    }

    public function findReportsByInternId(int $internId) {
        $currentUser = AuthenticatedUserHandler::getUser();

        if ($currentUser->role === UserRole::ADMIN->value) {
            $reports = $this->reportService->findReportByInternId($internId);
            return ['success' => true, 'message' => 'Reportes encontrados.', 'data' => $reports];
        }

        $internProfile = $this->internService->findInternById($internId);
        
        $isOwner = $internProfile->user->id === $currentUser->id;
        $isSupervisor = $internProfile->supervisor && $internProfile->supervisor->user->id === $currentUser->id;

        if (!$isOwner && !$isSupervisor) {
            throw ApiException::forbidden("No estás autorizado para ver los reportes de este practicante.");
        }

        $reports = $this->reportService->findReportByInternId($internId);
        return [
            'success' => true,
            'message' => 'Reportes encontrados.',
            'data' => $reports
        ];
    }

    public function findPendingForSupervisor(int $supervisorId) {
        $currentUser = AuthenticatedUserHandler::getUser();
        $supervisor = $this->supervisorRepository->findById($supervisorId);

        if (!$supervisor) {
            throw ApiException::notFound("Supervisor con id {$supervisorId} no encontrado.");
        }
        if ($supervisor->user_id !== $currentUser->id) {
            throw ApiException::forbidden("No estás autorizado para ver los reportes pendientes de otro supervisor.");
        }

        $reports = $this->reportService->findPendingReportsForSupervisor($supervisorId);
        return [
            'success' => true,
            'message' => 'Reportes pendientes recuperados.',
            'data' => $reports
        ];
    }

    public function reviewReport(int $id) {
        $currentUser = AuthenticatedUserHandler::getUser();
        $data = (array) json_decode(file_get_contents('php://input'), true);
        $dto = $this->validator->validateReview($data);

        $supervisor = $this->supervisorRepository->findById($dto->supervisorId);
        if (!$supervisor) {
            throw ApiException::notFound("Supervisor con id {$dto->supervisorId} no encontrado.");
        }
        if ($supervisor->user_id !== $currentUser->id) {
            throw ApiException::forbidden("No estás autorizado para revisar este reporte.");
        }

        $reviewedReport = $this->reportService->reviewReport($id, $dto);
        return [
            'success' => true,
            'message' => 'Reporte revisado exitosamente.',
            'data' => $reviewedReport
        ];
    }

    public function updateReportContent(int $id) {
        $currentUser = AuthenticatedUserHandler::getUser();
        $report = $this->reportService->findReportById($id);

        if ($report->intern->user->id !== $currentUser->id) {
            throw ApiException::forbidden("No estás autorizado para actualizar el contenido de este reporte.");
        }

        $data = (array) json_decode(file_get_contents('php://input'), true);
        $dto = $this->validator->validateUpdate($data);

        $updatedReport = $this->reportService->updateReportContent($id, $dto);
        return [
            'success' => true,
            'message' => 'Contenido del reporte actualizado.',
            'data' => $updatedReport
        ];
    }
}