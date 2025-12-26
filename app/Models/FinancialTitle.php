<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Db;

final class FinancialTitle
{
    /** @return array<int, array<string,mixed>> */
    public static function listByPeriod(int $tenantId, string $fromYmd, string $toYmd, ?string $type = null, ?string $status = null): array
    {
        $pdo = Db::pdo();

        $where = 'tenant_id = :tenant_id AND due_on BETWEEN :from AND :to';
        $params = ['tenant_id' => $tenantId, 'from' => $fromYmd, 'to' => $toYmd];

        if ($type !== null && in_array($type, ['payable', 'receivable'], true)) {
            $where .= ' AND type = :type';
            $params['type'] = $type;
        }

        if ($status !== null && in_array($status, ['open', 'paid', 'canceled'], true)) {
            $where .= ' AND status = :status';
            $params['status'] = $status;
        }

        $stmt = $pdo->prepare('SELECT * FROM financial_titles WHERE ' . $where . ' ORDER BY due_on DESC, id DESC');
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /** @return array{payable_open:int,receivable_open:int,net_open:int} */
    public static function openTotals(int $tenantId, string $fromYmd, string $toYmd): array
    {
        $pdo = Db::pdo();
        $stmt = $pdo->prepare(
            "SELECT
                SUM(CASE WHEN type='receivable' AND status='open' THEN amount_cents ELSE 0 END) AS receivable_open,
                SUM(CASE WHEN type='payable' AND status='open' THEN amount_cents ELSE 0 END) AS payable_open
             FROM financial_titles
             WHERE tenant_id = :tenant_id
               AND due_on BETWEEN :from AND :to"
        );
        $stmt->execute(['tenant_id' => $tenantId, 'from' => $fromYmd, 'to' => $toYmd]);
        $row = $stmt->fetch();
        $rec = is_array($row) && $row['receivable_open'] !== null ? (int)$row['receivable_open'] : 0;
        $pay = is_array($row) && $row['payable_open'] !== null ? (int)$row['payable_open'] : 0;
        return ['payable_open' => $pay, 'receivable_open' => $rec, 'net_open' => $rec - $pay];
    }

    public static function create(int $tenantId, string $type, int $amountCents, string $dueOn, ?string $category, ?string $description): void
    {
        $pdo = Db::pdo();
        $stmt = $pdo->prepare(
            'INSERT INTO financial_titles (tenant_id, type, status, category, description, amount_cents, due_on)
             VALUES (:tenant_id, :type, \"open\", :category, :description, :amount_cents, :due_on)'
        );
        $stmt->execute([
            'tenant_id' => $tenantId,
            'type' => $type,
            'category' => $category,
            'description' => $description,
            'amount_cents' => $amountCents,
            'due_on' => $dueOn,
        ]);
    }

    public static function markPaid(int $tenantId, int $id): void
    {
        $pdo = Db::pdo();
        $stmt = $pdo->prepare('UPDATE financial_titles SET status = \"paid\", paid_at = NOW(), updated_at = NOW() WHERE tenant_id = :tenant_id AND id = :id');
        $stmt->execute(['tenant_id' => $tenantId, 'id' => $id]);
    }

    public static function cancel(int $tenantId, int $id): void
    {
        $pdo = Db::pdo();
        $stmt = $pdo->prepare('UPDATE financial_titles SET status = \"canceled\", updated_at = NOW() WHERE tenant_id = :tenant_id AND id = :id');
        $stmt->execute(['tenant_id' => $tenantId, 'id' => $id]);
    }
}
