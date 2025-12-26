<?php

declare(strict_types=1);

$autoload = __DIR__ . '/../vendor/autoload.php';
if (is_file($autoload)) {
    require $autoload;
} else {
    spl_autoload_register(static function (string $class): void {
        $prefix = 'App\\';
        if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
            return;
        }

        $relative = substr($class, strlen($prefix));
        $path = __DIR__ . '/../app/' . str_replace('\\', '/', $relative) . '.php';
        if (is_file($path)) {
            require $path;
        }
    });
}

use App\Core\App;

$app = new App();
$app->run();
