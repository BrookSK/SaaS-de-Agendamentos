<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Db;

final class User
{
    public function __construct(
        public int $id,
        public ?int $tenantId,
        public string $name,
        public string $email,
        public string $passwordHash,
        public string $role
    ) {}

    public static function findByEmail(string $email, ?int $tenantId): ?self
    {
        $pdo = Db::pdo();

        if ($tenantId === null) {
            $stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email AND tenant_id IS NULL LIMIT 1');
            $stmt->execute(['email' => $email]);
        } else {
            $stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email AND tenant_id = :tenant_id LIMIT 1');
            $stmt->execute(['email' => $email, 'tenant_id' => $tenantId]);
        }

        $row = $stmt->fetch();
        if (!is_array($row)) {
            return null;
        }

        return new self(
            (int)$row['id'],
            $row['tenant_id'] !== null ? (int)$row['tenant_id'] : null,
            (string)$row['name'],
            (string)$row['email'],
            (string)$row['password_hash'],
            (string)$row['role']
        );
    }

    public static function findById(int $id): ?self
    {
        $pdo = Db::pdo();
        $stmt = $pdo->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        if (!is_array($row)) {
            return null;
        }

        return new self(
            (int)$row['id'],
            $row['tenant_id'] !== null ? (int)$row['tenant_id'] : null,
            (string)$row['name'],
            (string)$row['email'],
            (string)$row['password_hash'],
            (string)$row['role']
        );
    }

    public static function updatePasswordHash(int $id, string $passwordHash): void
    {
        $pdo = Db::pdo();
        $stmt = $pdo->prepare('UPDATE users SET password_hash = :password_hash, updated_at = NOW() WHERE id = :id');
        $stmt->execute(['password_hash' => $passwordHash, 'id' => $id]);
    }
}
