<?php
namespace Controllers;

require_once __DIR__ . '/../bootstrap.php';

class UploadController
{
    /**
     * POST /api/upload/run
     * 1リクエスト = 1ファイル
     * folderName 指定可
     */
    public function run()
    {
        // JWT必須
        $user = requireAuth();

        if (!isset($_FILES['file'])) {
            throw new \Exception('No file uploaded', 400);
        }

        $file = $_FILES['file'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new \Exception('Upload error', 400);
        }

        // サイズ制限（例: 20MB）
        $maxSize = 20 * 1024 * 1024;
        if ($file['size'] > $maxSize) {
            throw new \Exception('File too large', 413);
        }

        /**
         * フォルダ名取得 & サニタイズ
         */
        $folderName = $_POST['folderName'] ?? 'default';

        // 使用可能文字制限
        if (!preg_match('/^[a-zA-Z0-9_-]{1,50}$/', $folderName)) {
            throw new \Exception('Invalid folder name', 422);
        }

        // 保存先ディレクトリ
        $baseDir = UPLOAD_BASE_DIR;
        if ($baseDir === false) {
            throw new \Exception('Upload base directory not found', 500);
        }

        $uploadDir = $baseDir . '/' . $folderName;

        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0777, true)) {
                throw new \Exception('Failed to create directory', 500);
            }
        }

        $originalName = $file['name'];
        $tmpPath = $file['tmp_name'];

        // 拡張子取得
        $ext = pathinfo($originalName, PATHINFO_EXTENSION);

        // 衝突しない安全なファイル名
        $safeName = bin2hex(random_bytes(16));
        if ($ext) {
            $safeName .= '.' . $ext;
        }

        $destPath = $uploadDir . '/' . $safeName;

        if (!move_uploaded_file($tmpPath, $destPath)) {
            throw new \Exception('Failed to save file', 500);
        }

        return [
            'folder'        => $folderName,
            'original_name' => $originalName,
            'saved_as'      => $safeName,
            'size'          => $file['size'],
            'uploaded_by'   => $user->sub ?? null,
        ];
    }

    /**
     * GET /api/upload/folderList
     */
    public function folderList()
    {
        $user = requireAuth();

        $baseDir = UPLOAD_BASE_DIR;
        if ($baseDir === false) {
            throw new \Exception('Upload base directory not found', 500);
        }

        $dirs = array_filter(
            glob($baseDir . '/*'),
            'is_dir'
        );

        $folderNames = array_map(
            fn ($dir) => basename($dir),
            $dirs
        );

        sort($folderNames);

        return [
            'folders' => $folderNames,
        ];
    }

    /**
     * GET /api/upload/fileList
     * folderName を指定してファイル一覧取得
     */
    public function fileList()
    {
        $user = requireAuth();

        $folderName = $_GET['folderName'] ?? '';

        // folderName バリデーション（run と同じルール）
        if (!preg_match('/^[a-zA-Z0-9_-]{1,50}$/', $folderName)) {
            throw new \Exception('Invalid folder name', 422);
        }

        $targetDir = UPLOAD_BASE_DIR . '/' . $folderName;

        // フォルダが存在しない場合は空配列（正常系）
        if (!is_dir($targetDir)) {
            return [
                'files' => [],
            ];
        }

        $files = [];

        foreach (scandir($targetDir) as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            $fullPath = $targetDir . '/' . $entry;

            if (!is_file($fullPath)) {
                continue;
            }

            $pathInfo = pathinfo($entry);
            $mtime = filemtime($fullPath);

            $files[] = [
                'name' => $entry,
                'extension' => $pathInfo['extension'] ?? '',
                'updatedAt' => $mtime,
                'updatedAtIso' => date(DATE_ATOM, $mtime),
                // フロント用の論理パス
                'fullPath' => '/storage/uploads/' . $folderName . '/' . $entry,
            ];
        }

        // 最終更新日 降順（新しい順）
        usort($files, fn ($a, $b) => $b['updatedAt'] <=> $a['updatedAt']);

        return [
            'files' => $files,
        ];
    }

    /**
     * POST /api/upload/deleteFiles
     * 指定フォルダ内の複数ファイル削除
     */
    public function deleteFiles()
    {
        $user = requireAuth();

        $input = getJsonInput();

        $folderName = $input['folderName'] ?? '';
        $files = $input['files'] ?? [];

        // folderName バリデーション（run / fileList と同一）
        if (!preg_match('/^[a-zA-Z0-9_-]{1,50}$/', $folderName)) {
            throw new \Exception('Invalid folder name', 422);
        }

        if (!is_array($files) || empty($files)) {
            throw new \Exception('No files specified', 422);
        }

        $targetDir = UPLOAD_BASE_DIR . '/' . $folderName;

        // フォルダが存在しない場合
        if (!is_dir($targetDir)) {
            throw new \Exception('Target folder not found', 404);
        }

        $deleted = [];
        $errors = [];

        foreach ($files as $fileName) {
            // ファイル名バリデーション（パス注入防止）
            if (!preg_match('/^[^\/\\\\]+$/', $fileName)) {
                $errors[] = [
                    'file' => $fileName,
                    'reason' => 'Invalid file name',
                ];
                continue;
            }

            $filePath = $targetDir . '/' . $fileName;

            // 実ファイルのみ削除対象
            if (!is_file($filePath)) {
                $errors[] = [
                    'file' => $fileName,
                    'reason' => 'File not found',
                ];
                continue;
            }

            try {
                unlink($filePath);
                $deleted[] = $fileName;
            } catch (\Throwable $e) {
                $errors[] = [
                    'file' => $fileName,
                    'reason' => 'Delete failed',
                ];
            }
        }

        return [
            'deleted' => $deleted,
            'errors' => $errors,
        ];
    }
}
