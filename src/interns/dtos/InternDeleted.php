<?php

namespace App\interns\dtos;

class InternDeleted {
    public bool $success;
    public string $message;
    public int $deletedInternId;

    public function __construct(bool $success, string $message, int $deletedInternId) {
        $this->success = $success;
        $this->message = $message;
        $this->deletedInternId = $deletedInternId;
    }
}