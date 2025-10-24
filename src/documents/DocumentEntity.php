<?php

namespace App\documents;

enum DocumentType: String {
    case CV = "CV";
    case CERTIFICATE = "certificate";
    case REPORT = "report";
    case OTHER = "other";
}

class DocumentEntity {

    private $id;
    private $original_name;
    private $path;
    private $document_type;
    private $description;
    private $up_date;
    private $up_by_id;
    private $active;
    private $created_at;
}
