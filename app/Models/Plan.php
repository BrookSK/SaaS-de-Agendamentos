<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Db;

final class Plan
{
    public function __construct(
        public int $id,
        public string $name,
        public ?string $description,
        public int $priceCents,
        public string $billingCycle,
        public int $active
    ) {}

    /** @return array<int, self> */
    public static function all(): array
    {
        $pdo = Db::pdo();
        $stmt = $pdo->query('SELECT * FROM plans ORDER BY id DESC');
        $items = [];
        foreach ($stmt->fetchAll() as $row) {
            $items[] = new self(
                (int)$row['id'],
                (string)$row['name'],
                $row['description'] !== null ? (string)$row['description'] : null,
                (int)$row['price_cents'],
                (string)$row['billing_cycle'],
                (int)$row['active']
            );
        }
        return $items;
    }

    public static function findById(int $id): ?self
    {
        $pdo = Db::pdo();
        $stmt = $pdo->prepare('SELECT * FROM plans WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        if (!is_array($row)) {
            return null;
        }

        return new self(
            (int)$row['id'],
            (string)$row['name'],
            $row['description'] !== null ? (string)$row['description'] : null,
            (int)$row['price_cents'],
            (string)$row['billing_cycle'],
            (int)$row['active']
        );
    }

    public static function create(string $name, ?string $description, int $priceCents, string $billingCycle, int $active): void
    {
        $pdo = Db::pdo();
        $stmt = $pdo->prepare('INSERT INTO plans (name, description, price_cents, billing_cycle, active) VALUES (:name, :description, :price_cents, :billing_cycle, :active)');
        $stmt->execute([
            'name' => $name,
            'description' => $description,
            'price_cents' => $priceCents,
            'billing_cycle' => $billingCycle,
            'active' => $active,
        ]);
    }
}
