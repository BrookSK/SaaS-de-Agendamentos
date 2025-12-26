<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Db;

final class PasswordReset
{
    public static function create(int $userId, ?int $tenantId, string $tokenHash, string $expiresAt): void
    {
        $pdo = Db::pdo();
        $stmt = $pdo->prepare(
            'INSERT INTO password_resets (user_id, tenant_id, token_hash, expires_at)
             VALUES (:user_id, :tenant_id, :token_hash, :expires_at)'
        );
        $stmt->execute([
            'user_id' => $userId,
            'tenant_id' => $tenantId,
            'token_hash' => $tokenHash,
            'expires_at' => $expiresAt,
        ]);
    }

    /** @return array<string,mixed>|null */
    public static function findValidByTokenHash(string $tokenHash): ?array
    {
        $pdo = Db::pdo();
        $stmt = $pdo->prepare(
            'SELECT * FROM password_resets
             WHERE token_hash = :token_hash
               AND used_at IS NULL
               AND expires_at > NOW()
             ORDER BY id DESC
             LIMIT 1'
        );
        $stmt->execute(['token_hash' => $tokenHash]);
        $row = $stmt->fetch();
        return is_array($row) ? $row : null;
    }

    public static function markUsed(int $id): void
    {
        $pdo = Db::pdo();
        $stmt = $pdo->prepare('UPDATE password_resets SET used_at = NOW() WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}
