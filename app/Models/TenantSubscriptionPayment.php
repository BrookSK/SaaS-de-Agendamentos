<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Db;

final class TenantSubscriptionPayment
{
    /** @return array<int, array<string,mixed>> */
    public static function listBySubscription(int $subscriptionId): array
    {
        $pdo = Db::pdo();
        $stmt = $pdo->prepare('SELECT * FROM tenant_subscription_payments WHERE subscription_id = :subscription_id ORDER BY id DESC');
        $stmt->execute(['subscription_id' => $subscriptionId]);
        return $stmt->fetchAll();
    }

    public static function createManual(int $subscriptionId, int $amountCents, string $status = 'pending'): void
    {
        if (!in_array($status, ['pending', 'paid', 'failed', 'canceled'], true)) {
            $status = 'pending';
        }

        $pdo = Db::pdo();
        $stmt = $pdo->prepare(
            'INSERT INTO tenant_subscription_payments (subscription_id, provider, provider_payment_id, amount_cents, status, paid_at)
             VALUES (:subscription_id, :provider, NULL, :amount_cents, :status, :paid_at)'
        );

        $stmt->execute([
            'subscription_id' => $subscriptionId,
            'provider' => 'manual',
            'amount_cents' => $amountCents,
            'status' => $status,
            'paid_at' => $status === 'paid' ? date('Y-m-d H:i:s') : null,
        ]);
    }
}
