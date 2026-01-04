<?php
namespace Controllers;

require_once __DIR__ . '/../bootstrap.php';

use Firebase\JWT\JWT;

class AuthController
{
    /**
     * POST /api/auth/login
     */
    public function login(array $input)
    {
        $username = $input['username'] ?? '';
        $password = $input['password'] ?? '';

        if ($username === '' || $password === '') {
            throw new \Exception('username and password are required', 422);
        }

        // env から取得
        $envUser = $_ENV['ADMIN_USERNAME'] ?? '';
        $envPass = $_ENV['ADMIN_PASSWORD'] ?? '';

        if ($username !== $envUser || $password !== $envPass) {
            throw new \Exception('Invalid credentials', 401);
        }

        $payload = [
            'sub'  => $username,
            'role' => 'admin',
            'iat'  => time(),
            'exp'  => time() + 60 * 60 * 150, // 150時間
        ];

        $token = JWT::encode($payload, $_ENV['JWT_SECRET'], 'HS256');

        return [
            'token' => $token,
        ];
    }

    /**
     * GET /api/auth/me
     */
    public function me()
    {
        $user = requireAuth();
        return $user;
    }
}
