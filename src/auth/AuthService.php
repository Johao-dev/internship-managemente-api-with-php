<?php

namespace App\auth;

use App\core\ApiException;
use App\core\Mapper;
use App\users\UserService;
use App\users\UserRepository;
use App\auth\dtos\Login;
use App\auth\dtos\AuthResponse;
use App\auth\dtos\RegisterResponse;
use App\users\dtos\CreateUser;
use App\users\dtos\UserResponse;
use Firebase\JWT\JWT;

class AuthService {

    private UserRepository $userRepository;
    private UserService $userService;

    public function __construct() {
        $this->userRepository = new UserRepository();
        $this->userService = new UserService();
    }

    public function login(Login $loginDto): AuthResponse {
        $userEntity = $this->userRepository->findByEmail($loginDto->email);

        if (!$userEntity || !password_verify($loginDto->password, $userEntity->password)) {
            throw ApiException::unauthorized('Credenciales inválidas.');
        }

        if (!$userEntity->active) {
            throw ApiException::unauthorized('La cuenta del usuario está desactivada.');
        }

        $payload = [
            'id'    => $userEntity->id,
            'role'  => $userEntity->role,
            'iat'   => time(),
            'exp'   => time() + (60 * 60 * 8)
        ];
        $accessToken = $this->generateJwt($payload);

        $userResponse = Mapper::mapToDto(UserResponse::class, $userEntity);

        $authResponse = new AuthResponse();
        $authResponse->accessToken = $accessToken;
        $authResponse->user = $userResponse;
        
        return $authResponse;
    }

    public function register(CreateUser $registerDto): RegisterResponse {
        $newUser = $this->userService->createUser($registerDto);

        $response = new RegisterResponse();
        $response->message = "Usuario registrado exitosamente.";
        $response->user = $newUser;
        
        return $response;
    }

    private function generateJwt(array $payload): string {
        $secretKey = getenv('JWT_SECRET') ?: 'this-is-an-super-duper-password-12';
        return JWT::encode($payload, $secretKey, 'HS256');
    }
}