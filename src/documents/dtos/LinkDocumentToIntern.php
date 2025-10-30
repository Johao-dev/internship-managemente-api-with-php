<?php

namespace App\documents\dtos;

class LinkDocumentToIntern {
    public int $documentId;
    public int $internId;
    public string $relationType;
}