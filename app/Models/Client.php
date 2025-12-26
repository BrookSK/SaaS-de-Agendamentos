<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Db;

final class Client
{
    public function __construct(
        public int $id,
        public int $tenantId,
        public ?int $userId,
        public string $name,
        public ?string $phone,
        public ?string $email
    ) {}

    /** @return array<int, self> */
    public static function allByTenant(int $tenantId): array
    {
        $pdo = Db::pdo();
        $stmt = $pdo->prepare('SELECT * FROM clients WHERE tenant_id = :tenant_id ORDER BY id DESC');
        $stmt->execute(['tenant_id' => $tenantId]);

        $items = [];
        foreach ($stmt->fetchAll() as $row) {
            $items[] = new self(
                (int)$row['id'],
                (int)$row['tenant_id'],
                $row['user_id'] !== null ? (int)$row['user_id'] : null,
                (string)$row['name'],
                $row['phone'] !== null ? (string)$row['phone'] : null,
                $row['email'] !== null ? (string)$row['email'] : null
            );
        }

        return $items;
    }

    public static function create(int $tenantId, string $name, ?string $phone, ?string $email): int
    {
        $pdo = Db::pdo();
        $stmt = $pdo->prepare('INSERT INTO clients (tenant_id, user_id, name, phone, email) VALUES (:tenant_id, NULL, :name, :phone, :email)');
        $stmt->execute([
            'tenant_id' => $tenantId,
            'name' => $name,
            'phone' => $phone,
            'email' => $email,
        ]);

        return (int)$pdo->lastInsertId();
    }
}
