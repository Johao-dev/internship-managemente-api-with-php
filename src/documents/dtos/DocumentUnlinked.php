<?php

namespace App\documents\dtos;

class DocumentUnlinked {
    public bool $success;
    public string $message;
    public int $documentId;
    public int $internId;

    public function __construct(bool $success, string $message, int $documentId, int $internId) {
        $this->success = $success;
        $this->message = $message;
        $this->documentId = $documentId;
        $this->internId = $internId;
    }
}