<?php

abstract class BaseController
{
    protected function render(string $view, array $data = [], array $assets = []): void
    {
        $viewPath = __DIR__ . '/../views/' . $view . '.php';
        if (!file_exists($viewPath)) {
            throw new RuntimeException("View '{$view}' not found.");
        }

        $styles = $assets['styles'] ?? [];
        $scripts = $assets['scripts'] ?? [];
        $inlineScripts = $assets['inlineScripts'] ?? [];

        extract($data, EXTR_SKIP);

        require __DIR__ . '/../views/layouts/header.php';
        require $viewPath;
        require __DIR__ . '/../views/layouts/footer.php';
    }

    protected function jsonResponse($payload, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($payload);
        exit();
    }
}
