<?php

declare(strict_types=1);

return [
    'env' => getenv('APP_ENV') ?: 'local',
    'url' => getenv('APP_URL') ?: 'http://localhost',
];
