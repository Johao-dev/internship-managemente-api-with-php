<?php

namespace App\core;

use Exception;

class ApiException extends Exception {

    public function __construct($message, $code = 400, Exception $previous = null) {
        parent::__construct($message, $code, $previous);
    }

    public static function notFound(string $message = 'Recurso no encontrado.') {
        return new self($message, 404);
    }

    public static function unauthorized(string $message = 'No autorizado.') {
        return new self($message, 401);
    }

    public static function forbidden(string $message = 'Acceso denegado.') {
        return new self($message, 403);
    }

    public static function badRequest(string $message = 'Solicitud incorrecta.') {
        return new self($message, 400);
    }

    public static function conflict(string $message = "Ocurrio un conflicto.") {
        return new self($message, 409);
    }

    public static function internalServerError(string $message = "Ocurrio un error con el servidor.") {
        return new self($message, 500);
    }
}