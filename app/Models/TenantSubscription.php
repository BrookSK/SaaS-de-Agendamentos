<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Db;

final class TenantSubscription
{
    /** @return array<int, array<string,mixed>> */
    public static function listAll(): array
    {
        $pdo = Db::pdo();
        $stmt = $pdo->query(
            'SELECT ts.*, t.name AS tenant_name, t.slug AS tenant_slug, p.name AS plan_name
             FROM tenant_subscriptions ts
             INNER JOIN tenants t ON t.id = ts.tenant_id
             INNER JOIN plans p ON p.id = ts.plan_id
             ORDER BY ts.id DESC'
        );
        return $stmt->fetchAll();
    }

    public static function upsertForTenant(int $tenantId, int $planId, string $status): void
    {
        $pdo = Db::pdo();

        $stmt = $pdo->prepare('SELECT id FROM tenant_subscriptions WHERE tenant_id = :tenant_id ORDER BY id DESC LIMIT 1');
        $stmt->execute(['tenant_id' => $tenantId]);
        $row = $stmt->fetch();

        if (is_array($row)) {
            $upd = $pdo->prepare('UPDATE tenant_subscriptions SET plan_id = :plan_id, status = :status, updated_at = NOW() WHERE id = :id');
            $upd->execute([
                'plan_id' => $planId,
                'status' => $status,
                'id' => (int)$row['id'],
            ]);
            return;
        }

        $ins = $pdo->prepare('INSERT INTO tenant_subscriptions (tenant_id, plan_id, status, started_at) VALUES (:tenant_id, :plan_id, :status, NOW())');
        $ins->execute([
            'tenant_id' => $tenantId,
            'plan_id' => $planId,
            'status' => $status,
        ]);
    }

    /** @return array<string,mixed>|null */
    public static function latestByTenant(int $tenantId): ?array
    {
        $pdo = Db::pdo();
        $stmt = $pdo->prepare('SELECT * FROM tenant_subscriptions WHERE tenant_id = :tenant_id ORDER BY id DESC LIMIT 1');
        $stmt->execute(['tenant_id' => $tenantId]);
        $row = $stmt->fetch();
        return is_array($row) ? $row : null;
    }

    public static function setAsaasLink(int $subscriptionRowId, ?string $customerId, ?string $asaasSubscriptionId): void
    {
        $pdo = Db::pdo();
        $stmt = $pdo->prepare(
            'UPDATE tenant_subscriptions
             SET asaas_customer_id = :customer_id,
                 asaas_subscription_id = :subscription_id,
                 asaas_last_event_at = NOW(),
                 updated_at = NOW()
             WHERE id = :id'
        );
        $stmt->execute([
            'customer_id' => $customerId,
            'subscription_id' => $asaasSubscriptionId,
            'id' => $subscriptionRowId,
        ]);
    }
}
