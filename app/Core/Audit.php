<?php

declare(strict_types=1);

namespace App\Core;

use App\Models\AuditLog;

final class Audit
{
    /** @param array<string,mixed>|null $metadata */
    public static function log(string $action, ?string $entity = null, ?int $entityId = null, ?array $metadata = null): void
    {
        try {
            $tenantId = Tenant::current()?->tenantId;
            $user = Auth::user();
            $userId = is_array($user) ? (int)($user['id'] ?? 0) : null;
            if ($userId === 0) {
                $userId = null;
            }
            $ip = $_SERVER['REMOTE_ADDR'] ?? null;

            AuditLog::create($tenantId, $userId, $action, $entity, $entityId, $metadata, is_string($ip) ? $ip : null);
        } catch (\Throwable $e) {
            // Não derrubar o sistema por falha de log
        }
    }
}
