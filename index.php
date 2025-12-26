<?php

declare(strict_types=1);

// Fallback entrypoint for shared-hosting setups where the document root
// points to the project root instead of /public.
// Preferred: configure the server DocumentRoot to the /public folder.

$target = __DIR__ . '/public/index.php';

if (!is_file($target)) {
    http_response_code(500);
    echo 'public/index.php não encontrado.';
    exit;
}

// If you prefer redirect instead of include, replace with:
// header('Location: /public/'); exit;

require $target;
