<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Db;

final class TenantSubscriptionAsaas
{
    public static function updateFromWebhook(string $asaasSubscriptionId, ?string $status): void
    {
        $pdo = Db::pdo();
        $stmt = $pdo->prepare(
            'UPDATE tenant_subscriptions
             SET asaas_last_event_at = NOW(), asaas_last_status = :status
             WHERE asaas_subscription_id = :asaas_subscription_id'
        );
        $stmt->execute([
            'status' => $status,
            'asaas_subscription_id' => $asaasSubscriptionId,
        ]);
    }
}
