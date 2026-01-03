<?php

/**
 * 共通：JSONレスポンスを返して終了
 */
function jsonResponse(
    mixed $data,
    int $status = 200,
    bool $success = true,
    ?string $errorCode = null
): void {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');

    if ($success) {
        $body = [
            'success' => true,
            'data'    => $data,
        ];
    } else {
        $body = [
            'success' => false,
            'message' => $data,
        ];
        if ($errorCode) {
            $body['code'] = $errorCode;
        }
    }

    echo json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/**
 * 成功レスポンス
 */
function success(mixed $data = null, int $status = 200): void
{
    jsonResponse($data, $status, true);
}

/**
 * エラーレスポンス
 */
function error(string $message, int $status = 400, ?string $code = null): void
{
    jsonResponse($message, $status, false, $code);
}
