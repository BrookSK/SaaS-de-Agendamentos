<?php

declare(strict_types=1);

namespace App\Core;

final class View
{
    public static function render(string $view, array $data = []): string
    {
        $path = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . $view . '.php';
        if (!is_file($path)) {
            return 'View não encontrada: ' . htmlspecialchars($view);
        }

        extract($data);
        ob_start();
        require $path;
        $html = (string)ob_get_clean();

        if (stripos($html, '<head') !== false && stripos($html, 'rel="stylesheet"') === false) {
            $link = "\n    <link rel=\"stylesheet\" href=\"/assets/app.css\">\n";
            $html = preg_replace('/<head(\b[^>]*)>/i', '<head$1>' . $link, $html, 1) ?? $html;
        }

        return $html;
    }
}
