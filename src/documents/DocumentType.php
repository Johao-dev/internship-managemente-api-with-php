<?php

namespace App\documents;

enum DocumentType: String {
    case CV = "CV";
    case CERTIFICATE = "certificate";
    case REPORT = "report";
    case OTHER = "other";
}