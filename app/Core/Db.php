<?php

declare(strict_types=1);

namespace App\Core;

use PDO;

final class Db
{
    private static ?PDO $pdo = null;

    public static function pdo(): PDO
    {
        if (self::$pdo !== null) {
            return self::$pdo;
        }

        $cfg = require dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'database.php';

        $env = (string)($cfg['environment'] ?? 'test');
        $connections = (array)($cfg['connections'] ?? []);
        $conn = $connections[$env] ?? null;
        if (!is_array($conn)) {
            throw new \RuntimeException('Config de banco inválida para environment: ' . $env);
        }

        $charset = (string)($cfg['charset'] ?? 'utf8mb4');

        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $conn['host'],
            (int)$conn['port'],
            $conn['name'],
            $charset
        );

        self::$pdo = new PDO($dsn, (string)$conn['user'], (string)$conn['pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);

        return self::$pdo;
    }
}
