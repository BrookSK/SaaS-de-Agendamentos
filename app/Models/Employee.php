<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Db;

final class Employee
{
    public function __construct(
        public int $id,
        public int $tenantId,
        public ?int $userId,
        public string $name,
        public int $active
    ) {}

    /** @return array<int, self> */
    public static function allByTenant(int $tenantId): array
    {
        $pdo = Db::pdo();
        $stmt = $pdo->prepare('SELECT * FROM employees WHERE tenant_id = :tenant_id ORDER BY id DESC');
        $stmt->execute(['tenant_id' => $tenantId]);

        $items = [];
        foreach ($stmt->fetchAll() as $row) {
            $items[] = new self(
                (int)$row['id'],
                (int)$row['tenant_id'],
                $row['user_id'] !== null ? (int)$row['user_id'] : null,
                (string)$row['name'],
                (int)$row['active']
            );
        }

        return $items;
    }

    public static function create(int $tenantId, string $name): void
    {
        $pdo = Db::pdo();
        $stmt = $pdo->prepare('INSERT INTO employees (tenant_id, user_id, name, active) VALUES (:tenant_id, NULL, :name, 1)');
        $stmt->execute([
            'tenant_id' => $tenantId,
            'name' => $name,
        ]);
    }
}
