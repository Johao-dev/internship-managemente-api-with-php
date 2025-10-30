<?php

namespace App\documents\dtos;

use App\interns\dtos\InternResponse;

class InternDocumentResponse {
    public int $id;
    public int $documentId;
    public int $internId;
    public string $relationType;
    public InternResponse $intern;
}