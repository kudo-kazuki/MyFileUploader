<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/**
 * JSON入力を取得
 * - JSONでない / 空の場合は空配列
 */
function getJsonInput(): array
{
    $raw = file_get_contents('php://input');
    if ($raw === false || $raw === '') {
        return [];
    }

    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

/**
 * Authorization ヘッダから Bearer トークンを取得
 */
function getBearerToken(): string
{
    $headers = getallheaders();

    $authHeader =
        $headers['Authorization']
        ?? $headers['authorization']
        ?? ($_SERVER['HTTP_AUTHORIZATION'] ?? '');

    if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
        throw new Exception('Missing Authorization header', 401);
    }

    return substr($authHeader, 7);
}

/**
 * JWTを検証してユーザー情報を返す
 */
function getAuthenticatedUser(): object
{
    $token = getBearerToken();

    if (empty($_ENV['JWT_SECRET'])) {
        throw new Exception('JWT secret not configured', 500);
    }

    try {
        return JWT::decode($token, new Key($_ENV['JWT_SECRET'], 'HS256'));
    } catch (Exception $e) {
        // 理由は外に出さない
        throw new Exception('Unauthorized', 401);
    }
}

/**
 * 認証必須（role問わず）
 */
function requireAuth(): object
{
    return getAuthenticatedUser();
}

/**
 * 管理者専用
 */
function requireAdmin(): object
{
    $user = requireAuth();

    if (($user->role ?? null) !== 'admin') {
        throw new Exception('Forbidden (admin only)', 403);
    }

    return $user;
}

/**
 * 一般ユーザー専用
 */
function requireUser(): object
{
    $user = requireAuth();

    if (($user->role ?? null) !== 'user') {
        throw new Exception('Forbidden (user only)', 403);
    }

    return $user;
}
