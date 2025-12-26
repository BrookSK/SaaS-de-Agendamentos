<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Db;

final class SystemSetting
{
    public static function get(string $key, ?string $default = null): ?string
    {
        $pdo = Db::pdo();
        $stmt = $pdo->prepare('SELECT value FROM system_settings WHERE `key` = :key LIMIT 1');
        $stmt->execute(['key' => $key]);
        $row = $stmt->fetch();
        if (!is_array($row)) {
            return $default;
        }
        return $row['value'] !== null ? (string)$row['value'] : $default;
    }

    /** @param array<string, string|null> $values */
    public static function setMany(array $values): void
    {
        $pdo = Db::pdo();
        $stmt = $pdo->prepare(
            'INSERT INTO system_settings (`key`, `value`) VALUES (:key, :value)
             ON DUPLICATE KEY UPDATE `value` = VALUES(`value`), updated_at = CURRENT_TIMESTAMP'
        );

        foreach ($values as $key => $value) {
            $stmt->execute([
                'key' => $key,
                'value' => $value,
            ]);
        }
    }

    /** @param array<int, string> $keys @return array<string, string|null> */
    public static function getMany(array $keys): array
    {
        if ($keys === []) {
            return [];
        }

        $pdo = Db::pdo();
        $in = implode(',', array_fill(0, count($keys), '?'));
        $stmt = $pdo->prepare('SELECT `key`, `value` FROM system_settings WHERE `key` IN (' . $in . ')');
        $stmt->execute(array_values($keys));

        $out = [];
        foreach ($keys as $k) {
            $out[$k] = null;
        }

        foreach ($stmt->fetchAll() as $row) {
            $out[(string)$row['key']] = $row['value'] !== null ? (string)$row['value'] : null;
        }

        return $out;
    }
}
