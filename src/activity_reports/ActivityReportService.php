<?php

namespace App\activity_reports;

use App\core\ApiException;
use App\core\Mapper;
use App\activity_reports\dtos\NewReportSubmitted;
use App\activity_reports\dtos\ReportResponse;
use App\activity_reports\dtos\ReportReviewed;
use App\activity_reports\dtos\ReportUpdated;
use App\activity_reports\dtos\ReviewReport;
use App\activity_reports\dtos\SubmitReport;
use App\activity_reports\dtos\UpdateReport;
use App\interns\InternService;
use App\users\dtos\SupervisorResponse;
use App\users\SupervisorRepository;
use App\users\UserRepository;

class ActivityReportService {

    private ActivityReportRepository $reportRepository;
    private InternService $internService;
    private SupervisorRepository $supervisorRepository;
    private UserRepository $userRepository;

    public function __construct() {
        $this->reportRepository = new ActivityReportRepository();
        $this->internService = new InternService();
        $this->supervisorRepository = new SupervisorRepository();
        $this->userRepository = new UserRepository();
    }

    public function submitReport(SubmitReport $submitDto): NewReportSubmitted {
        $intern = $this->internService->findInternById($submitDto->internId);

        if (!$intern->supervisor) {
            throw ApiException::badRequest("No puedes enviar reportes porque no tienes un supervisor asignado.");
        }

        $newReport = new ActivityReportEntity();
        $newReport->intern_id = $submitDto->internId;
        $newReport->supervisor_id = $intern->supervisor->id;
        $newReport->title = $submitDto->title;
        $newReport->content = $submitDto->content;
        $newReport->send_date = date('Y-m-d H:i:s');
        $newReport->revision_state = RevisionState::PENDING->value;
        $newReport->revision_date = null;
        $newReport->supervisor_comment = null;

        // 3. Guardar y obtener el nuevo ID
        // Como se discutió, este método create() modificado debe devolver el ID
        $newReportId = $this->reportRepository->createAndGetId($newReport);
        if ($newReportId === 0) {
            throw ApiException::internalServerError("No se pudo guardar el reporte.");
        }

        $savedReport = $this->findReportOrFail($newReportId);
        $response = $this->buildReportResponse($savedReport);
        
        return Mapper::mapToDto(NewReportSubmitted::class, $response);
    }

    public function findReportById(int $id): ReportResponse {
        $report = $this->findReportOrFail($id);
        return $this->buildReportResponse($report);
    }

    public function findReportByInternId(int $internId): array {
        $reports = $this->reportRepository->findByInternId($internId);
        
        $responseArray = [];
        foreach ($reports as $report) {
            $responseArray[] = $this->buildReportResponse($report);
        }
        return $responseArray;
    }

    public function findPendingReportsForSupervisor(int $supervisorId): array {
        $reports = $this->reportRepository->findPendingBySupervisorId($supervisorId);
        
        $responseArray = [];
        foreach ($reports as $report) {
            $responseArray[] = $this->buildReportResponse($report);
        }
        return $responseArray;
    }

    public function reviewReport(int $reportId, ReviewReport $reviewDto): ReportReviewed {
        $report = $this->findReportOrFail($reportId);

        if ($report->supervisor_id !== $reviewDto->supervisorId) {
            throw ApiException::badRequest("El reporte con id {$reportId} está asignado a un supervisor diferente.");
        }

        $report->revision_state = $reviewDto->revisionState;
        $report->supervisor_comment = $reviewDto->supervisorComment ?? "";
        $report->revision_date = date('Y-m-d H:i:s');

        $this->reportRepository->update($report);

        $response = $this->buildReportResponse($report);
        return Mapper::mapToDto(ReportReviewed::class, $response);
    }

    public function updateReportContent(int $reportId, UpdateReport $updateDto): ReportUpdated {
        $report = $this->findReportOrFail($reportId);

        if ($report->revision_state !== RevisionState::REVISION_REQUIRED->value) {
            throw ApiException::badRequest("El reporte con id {$reportId} no puede ser actualizado en su estado actual ({$report->revision_state}).");
        }
        
        if ($updateDto->content !== null) {
            $report->content = $updateDto->content;
        }
        
        $report->revision_state = RevisionState::PENDING->value;

        $this->reportRepository->update($report);

        $response = $this->buildReportResponse($report);
        return Mapper::mapToDto(ReportUpdated::class, $response);
    }

    private function findReportOrFail(int $id): ActivityReportEntity {
        $report = $this->reportRepository->findById($id);
        if (!$report) {
            throw ApiException::notFound("Reporte con id {$id} no encontrado.");
        }
        return $report;
    }

    private function buildReportResponse(ActivityReportEntity $report): ReportResponse {
        $response = Mapper::mapToDto(ReportResponse::class, $report);

        $response->intern = $this->internService->findInternById($report->intern_id);

        if ($report->supervisor_id) {
            $supervisorEntity = $this->supervisorRepository->findById($report->supervisor_id);
            if ($supervisorEntity) {
                $supervisorUser = $this->userRepository->findById($supervisorEntity->user_id);
                
                $supervisorDto = new SupervisorResponse();
                $supervisorDto->id = $supervisorEntity->id;
                $supervisorDto->area = $supervisorEntity->area;
                $supervisorDto->user = Mapper::mapToDto(\App\users\dtos\UserResponse::class, $supervisorUser);
                
                $response->supervisor = $supervisorDto;
            }
        }

        return $response;
    }
}