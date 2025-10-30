<?php

namespace App\documents\dtos;

use App\users\dtos\UserResponse;

class DocumentResponse {
    public int $id;
    public string $originalName;
    public string $path;
    public string $documentType;
    public ?string $description = null;
    public string $upDate;
    public int $upById;
    public bool $active;
    public UserResponse $upBy;
    
    /** @var InternDocumentResponse[] */
    public array $internLinks = [];
}