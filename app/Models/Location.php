<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Db;

final class Location
{
    public function __construct(
        public int $id,
        public int $tenantId,
        public string $name,
        public ?string $address,
        public ?string $phone,
        public int $active
    ) {}

    /** @return array<int, self> */
    public static function allByTenant(int $tenantId): array
    {
        $pdo = Db::pdo();
        $stmt = $pdo->prepare('SELECT * FROM locations WHERE tenant_id = :tenant_id ORDER BY id DESC');
        $stmt->execute(['tenant_id' => $tenantId]);

        $items = [];
        foreach ($stmt->fetchAll() as $row) {
            $items[] = new self(
                (int)$row['id'],
                (int)$row['tenant_id'],
                (string)$row['name'],
                $row['address'] !== null ? (string)$row['address'] : null,
                $row['phone'] !== null ? (string)$row['phone'] : null,
                (int)$row['active']
            );
        }

        return $items;
    }

    public static function create(int $tenantId, string $name, ?string $address, ?string $phone): void
    {
        $pdo = Db::pdo();
        $stmt = $pdo->prepare('INSERT INTO locations (tenant_id, name, address, phone, active) VALUES (:tenant_id, :name, :address, :phone, 1)');
        $stmt->execute([
            'tenant_id' => $tenantId,
            'name' => $name,
            'address' => $address,
            'phone' => $phone,
        ]);
    }

    public static function deleteById(int $tenantId, int $id): void
    {
        $pdo = Db::pdo();
        $stmt = $pdo->prepare('DELETE FROM locations WHERE tenant_id = :tenant_id AND id = :id');
        $stmt->execute([
            'tenant_id' => $tenantId,
            'id' => $id,
        ]);
    }
}
