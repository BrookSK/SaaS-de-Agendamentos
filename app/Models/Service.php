<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Db;

final class Service
{
    public function __construct(
        public int $id,
        public int $tenantId,
        public string $name,
        public int $durationMinutes,
        public int $priceCents,
        public int $active
    ) {}

    /** @return array<int, self> */
    public static function allByTenant(int $tenantId): array
    {
        $pdo = Db::pdo();
        $stmt = $pdo->prepare('SELECT * FROM services WHERE tenant_id = :tenant_id ORDER BY id DESC');
        $stmt->execute(['tenant_id' => $tenantId]);

        $items = [];
        foreach ($stmt->fetchAll() as $row) {
            $items[] = new self(
                (int)$row['id'],
                (int)$row['tenant_id'],
                (string)$row['name'],
                (int)$row['duration_minutes'],
                (int)$row['price_cents'],
                (int)$row['active']
            );
        }

        return $items;
    }

    public static function create(int $tenantId, string $name, int $durationMinutes, int $priceCents): void
    {
        $pdo = Db::pdo();
        $stmt = $pdo->prepare('INSERT INTO services (tenant_id, name, duration_minutes, price_cents, active) VALUES (:tenant_id, :name, :duration_minutes, :price_cents, 1)');
        $stmt->execute([
            'tenant_id' => $tenantId,
            'name' => $name,
            'duration_minutes' => $durationMinutes,
            'price_cents' => $priceCents,
        ]);
    }
}
