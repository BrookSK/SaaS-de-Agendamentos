<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Db;

final class SystemWebhook
{
    /** @return array<int, array<string,mixed>> */
    public static function all(): array
    {
        $pdo = Db::pdo();
        $stmt = $pdo->query('SELECT * FROM system_webhooks ORDER BY id DESC');
        return $stmt->fetchAll();
    }

    public static function create(string $eventName, string $url, ?string $secret, string $environment, int $active): void
    {
        $pdo = Db::pdo();
        $stmt = $pdo->prepare(
            'INSERT INTO system_webhooks (event_name, url, secret, environment, active)
             VALUES (:event_name, :url, :secret, :environment, :active)'
        );
        $stmt->execute([
            'event_name' => $eventName,
            'url' => $url,
            'secret' => $secret,
            'environment' => $environment,
            'active' => $active,
        ]);
    }
}
