<?php

declare(strict_types=1);

namespace App\Core;

final class Url
{
    public static function base(?string $fallback = null): string
    {
        $host = $_SERVER['HTTP_HOST'] ?? ($_SERVER['SERVER_NAME'] ?? null);
        if (!is_string($host) || $host === '') {
            return $fallback ?? 'http://localhost';
        }

        $https = $_SERVER['HTTPS'] ?? null;
        $isHttps = ($https === 'on' || $https === '1');

        $proto = $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? null;
        if (is_string($proto) && ($proto === 'https' || $proto === 'http')) {
            $isHttps = $proto === 'https';
        }

        $scheme = $isHttps ? 'https' : 'http';

        $script = $_SERVER['SCRIPT_NAME'] ?? '';
        $dir = '';
        if (is_string($script) && $script !== '') {
            $dir = rtrim(str_replace('\\', '/', dirname($script)), '/');
            if ($dir === '.') {
                $dir = '';
            }
        }

        return $scheme . '://' . $host . $dir;
    }
}
