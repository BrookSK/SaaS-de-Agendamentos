<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Db;

final class AsaasSetting
{
    /** @return array<string, string|null> */
    public static function get(): array
    {
        $pdo = Db::pdo();
        $stmt = $pdo->query('SELECT * FROM asaas_settings WHERE id = 1 LIMIT 1');
        $row = $stmt->fetch();
        if (!is_array($row)) {
            return [
                'environment' => 'sandbox',
                'api_key_sandbox' => null,
                'api_key_production' => null,
                'webhook_token' => null,
            ];
        }

        return [
            'environment' => (string)($row['environment'] ?? 'sandbox'),
            'api_key_sandbox' => $row['api_key_sandbox'] !== null ? (string)$row['api_key_sandbox'] : null,
            'api_key_production' => $row['api_key_production'] !== null ? (string)$row['api_key_production'] : null,
            'webhook_token' => $row['webhook_token'] !== null ? (string)$row['webhook_token'] : null,
        ];
    }

    public static function update(string $environment, ?string $sandboxKey, ?string $productionKey, ?string $webhookToken): void
    {
        $pdo = Db::pdo();
        $stmt = $pdo->prepare(
            'UPDATE asaas_settings
             SET environment = :environment,
                 api_key_sandbox = :api_key_sandbox,
                 api_key_production = :api_key_production,
                 webhook_token = :webhook_token,
                 updated_at = NOW()
             WHERE id = 1'
        );
        $stmt->execute([
            'environment' => $environment,
            'api_key_sandbox' => $sandboxKey,
            'api_key_production' => $productionKey,
            'webhook_token' => $webhookToken,
        ]);
    }

    public static function currentApiKey(): ?string
    {
        $s = self::get();
        return $s['environment'] === 'production' ? $s['api_key_production'] : $s['api_key_sandbox'];
    }
}
