<?php

namespace App\documents;

enum DocumentType: String {
    case CV = "CV";
    case CERTIFICATE = "certificate";
    case REPORT = "report";
    case OTHER = "other";
}

class DocumentEntity {

    public $id;
    public $original_name;
    public $path;
    public $document_type;
    public $description;
    public $up_date;
    public $up_by_id;
    public $active;
    public $created_at;
}
