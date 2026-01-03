<?php
namespace Controllers;

require_once __DIR__ . '/../bootstrap.php';

use Firebase\JWT\JWT;

class TestController
{
    public function message()
    {
        // Router が success() で包むので、返すだけでOK
        return 'あああ';
    }

    /**
     * GET /api/test/auth
     * JWT必須：トークン検証の動作確認
     */
    public function auth()
    {
        // JWT必須（失敗したら 401）
        $user = requireAuth();

        // トークンが正しくデコードできていればここに来る
        return [
            'message' => 'JWT OK',
            'user' => $user, // payload をそのまま返す（確認用）
        ];
    }

    /**
     * POST /api/test/upload
     * JWT必須：upload ダミー（実ファイル保存はしない）
     */
    public function upload()
    {
        // 認証必須
        $user = requireAuth();

        // PHP に届いているファイル情報を確認
        if (empty($_FILES)) {
            throw new \Exception('No file uploaded', 400);
        }

        // 今回はダミーなので最初の1件だけ見る
        $file = reset($_FILES);

        return [
            'message' => 'Upload received (dummy)',
            'filename' => $file['name'] ?? null,
            'size' => $file['size'] ?? null,
            'type' => $file['type'] ?? null,
            'user' => $user,
        ];
    }
}
