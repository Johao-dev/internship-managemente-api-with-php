<?php

namespace App\documents\dtos;

class UploadDocument {
    public string $documentType;
    public ?string $description = null;
    public int $upById;
}