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
        return (string)ob_get_clean();
    }
}
