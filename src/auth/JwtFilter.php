<?php

namespace App\auth;

use App\core\ApiException;
use App\core\AuthenticatedUserHandler;
use App\users\UserRepository;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Exception;

class JwtFilter {

    private static $publicRoutes = [
        'auth' => ['login', 'register']
    ];

    public static function handle() {
        $resource = $_GET['resource'] ?? null;
        $operation = $_GET['op'] ?? null;

        if (isset(self::$publicRoutes[$resource]) && in_array($operation, self::$publicRoutes[$resource])) {
            return;
        }

        $token = self::getBearerToken();
        if (!$token) {
            throw ApiException::unauthorized('Token no proporcionado.');
        }

        try {
            $secretKey = $_ENV['JWT_SECRET'];
            $payload = JWT::decode($token, new Key($secretKey, 'HS256'));

            if (!isset($payload->id)) {
                throw ApiException::forbidden("Payload de JWT invalido. Falta 'id'.");
            }

            $userRepository = new UserRepository();
            $user = $userRepository->findById($payload->id);

            if (!$user) {
                throw ApiException::unauthorized('Usuario del token no encontrado.');
            }

            if (!$user->active) {
                throw ApiException::unauthorized("La cuenta del usuario esta desactivada.");
            }

            AuthenticatedUserHandler::setUser($user);
        } catch (Exception $ex) {
            throw ApiException::unauthorized('Token invalido o expirado. ' . $ex->getMessage());
        }
    }

    private static function getBearerToken() {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION']
            ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION']
            ?? null;

        if (!$authHeader && function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
            if (isset($headers['Authorization'])) {
                $authHeader = $headers['Authorization'];
            }
        }

        if ($authHeader && preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return $matches[1];
        }

        return null;
    }

}