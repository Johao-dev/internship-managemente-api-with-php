<?php

namespace App\auth;

use App\core\ApiException;
use App\core\AuthenticatedUserHandler;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Exception;

class JwtFilter {

    private static $publicRoutes = [
        'auth' => ['login', 'register'] //ej. api.php?resource=auth&op=login
    ];

    public static function handle() {
        $resource = $_GET['resource'] ?? null;
        $operation = $_GET['op'] ?? null;

        // verifica que la ruta es publica
        if (isset(self::$publicRoutes[$resource]) && in_array($operation, self::$publicRoutes[$resource])) {
            return; // es publica, no se necesita token
        }

        // si no es publica, existe un token
        $token = self::getBearerToken();
        if (!$token) {
            throw ApiException::unauthorized('Token no proporcionado.');
        }

        try {
            $secretKey = getenv('JWT_SECRET' ?: 'this-is-an-super-duper-password-12');
            $payload = JWT::decode($token, new Key($secretKey, 'HS256'));

            // 3. El token es válido. Buscar al usuario en la BD
            // (Aquí deberías usar tu UserRepository)
            // $userRepository = new \App\users\UserRepository();
            // $user = $userRepository->findById($payload->sub); // 'sub' es el ID de usuario

            // --- INICIO: Simulación mientras creas UserRepository ---
            if (!isset($payload->sub) || !isset($payload->role)) {
                throw new Exception('Payload de JWT inválido.');
            }
            $user = (object)[
                'id' => $payload->sub,
                'role' => $payload->role,
                'email' => $payload->email
            ];
            // --- FIN: Simulación ---

            if (!$user) {
                throw ApiException::unauthorized('Usuario del token no encontrado.');
            }

            AuthenticatedUserHandler::setUser($user);
        } catch (Exception $ex) {
            throw ApiException::unauthorized('Token invalido o expirado. ' . $ex->getMessage());
        }
    }

    private static function getBearerToken() {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? null;
        if ($authHeader) {
            if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
                return $matches[1];
            }
        }
        return null;
    }
}