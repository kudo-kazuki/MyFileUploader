<?php
namespace Core;

use Exception;

class Router
{
    public function dispatch(string $uri, string $method = 'GET'): void
    {
        // /api/ 以降を取得
        $path = parse_url($uri, PHP_URL_PATH);
        $relative = preg_replace('#^/api/#', '', $path);
        [$controllerName, $actionName] = explode('/', $relative) + [null, null];

        if (!$controllerName || !$actionName) {
            throw new Exception('Invalid route', 404);
        }

        // クラス名 & ファイルパスを構築
        $class = 'Controllers\\' . ucfirst($controllerName) . 'Controller';
        $file = __DIR__ . '/Controllers/' . ucfirst($controllerName) . 'Controller.php';

        if (!file_exists($file)) {
            throw new Exception('Controller not found', 404);
        }

        require_once $file;

        if (!class_exists($class)) {
            throw new Exception('Controller class not found', 500);
        }

        $controller = new $class;

        if (!method_exists($controller, $actionName)) {
            throw new Exception('Action not found', 404);
        }

        // JSON入力を取得（POSTなど）
        $input = getJsonInput();

        // 実行
        $result = $controller->$actionName($input);

        // 成功レスポンス
        success($result);
    }
}
