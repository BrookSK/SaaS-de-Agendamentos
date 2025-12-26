<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Db;

final class FinancialTransaction
{
    /** @return array<int, array<string,mixed>> */
    public static function listByPeriod(int $tenantId, string $fromYmd, string $toYmd): array
    {
        $pdo = Db::pdo();
        $stmt = $pdo->prepare(
            'SELECT * FROM financial_transactions
             WHERE tenant_id = :tenant_id
               AND occurred_on BETWEEN :from AND :to
             ORDER BY occurred_on DESC, id DESC'
        );
        $stmt->execute([
            'tenant_id' => $tenantId,
            'from' => $fromYmd,
            'to' => $toYmd,
        ]);
        return $stmt->fetchAll();
    }

    /** @return array{in_cents:int,out_cents:int,balance_cents:int} */
    public static function totalsByPeriod(int $tenantId, string $fromYmd, string $toYmd): array
    {
        $pdo = Db::pdo();
        $stmt = $pdo->prepare(
            "SELECT
                SUM(CASE WHEN type='in' THEN amount_cents ELSE 0 END) AS in_cents,
                SUM(CASE WHEN type='out' THEN amount_cents ELSE 0 END) AS out_cents
             FROM financial_transactions
             WHERE tenant_id = :tenant_id
               AND occurred_on BETWEEN :from AND :to"
        );
        $stmt->execute([
            'tenant_id' => $tenantId,
            'from' => $fromYmd,
            'to' => $toYmd,
        ]);
        $row = $stmt->fetch();

        $in = is_array($row) && $row['in_cents'] !== null ? (int)$row['in_cents'] : 0;
        $out = is_array($row) && $row['out_cents'] !== null ? (int)$row['out_cents'] : 0;
        return ['in_cents' => $in, 'out_cents' => $out, 'balance_cents' => $in - $out];
    }

    public static function create(int $tenantId, string $type, int $amountCents, string $occurredOn, ?string $category, ?string $description): void
    {
        $pdo = Db::pdo();
        $stmt = $pdo->prepare(
            'INSERT INTO financial_transactions (tenant_id, type, category, description, amount_cents, occurred_on)
             VALUES (:tenant_id, :type, :category, :description, :amount_cents, :occurred_on)'
        );
        $stmt->execute([
            'tenant_id' => $tenantId,
            'type' => $type,
            'category' => $category,
            'description' => $description,
            'amount_cents' => $amountCents,
            'occurred_on' => $occurredOn,
        ]);
    }
}
