<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Core\Auth;
use App\Core\Audit;
use App\Core\Request;
use App\Core\Response;
use App\Core\Tenant;
use App\Core\WebhookDispatcher;
use App\Models\FinancialTransaction;

final class TenantFinanceController extends Controller
{
    /** @param array<string,string> $params */
    public function index(Request $request, array $params): Response
    {
        $tenant = Tenant::current();
        if ($tenant === null || $tenant->tenantId === null) {
            return Response::html('Tenant inválido', 400);
        }

        if ($resp = Auth::requireRole('tenant_admin', 'employee')) {
            return $resp;
        }

        $from = (string)$request->query('from', date('Y-m-01'));
        $to = (string)$request->query('to', date('Y-m-t'));

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $from) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $to)) {
            $from = date('Y-m-01');
            $to = date('Y-m-t');
        }

        return $this->view('tenant/finance/index', [
            'tenant' => $tenant,
            'from' => $from,
            'to' => $to,
            'totals' => FinancialTransaction::totalsByPeriod($tenant->tenantId, $from, $to),
            'items' => FinancialTransaction::listByPeriod($tenant->tenantId, $from, $to),
            'message' => $request->query('message'),
            'error' => $request->query('error'),
        ]);
    }

    /** @param array<string,string> $params */
    public function store(Request $request, array $params): Response
    {
        $tenant = Tenant::current();
        if ($tenant === null || $tenant->tenantId === null) {
            return Response::html('Tenant inválido', 400);
        }

        if ($resp = Auth::requireRole('tenant_admin', 'employee')) {
            return $resp;
        }

        $type = (string)$request->input('type', 'in');
        $amount = (int)$request->input('amount_cents', 0);
        $occurredOn = (string)$request->input('occurred_on', date('Y-m-d'));
        $category = trim((string)$request->input('category', ''));
        $description = trim((string)$request->input('description', ''));

        if (!in_array($type, ['in', 'out'], true)) {
            $type = 'in';
        }

        if ($amount <= 0 || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $occurredOn)) {
            return Response::redirect($tenant->urlPrefix() . '/finance?error=' . rawurlencode('Dados inválidos'));
        }

        FinancialTransaction::create(
            $tenant->tenantId,
            $type,
            $amount,
            $occurredOn,
            $category !== '' ? $category : null,
            $description !== '' ? $description : null
        );

        Audit::log('finance.transaction.create', 'financial_transaction', null, [
            'type' => $type,
            'amount_cents' => $amount,
            'occurred_on' => $occurredOn,
        ]);

        WebhookDispatcher::dispatch('finance.transaction.created', [
            'tenant' => [
                'id' => $tenant->tenantId,
                'slug' => $tenant->slug,
            ],
            'transaction' => [
                'type' => $type,
                'amount_cents' => $amount,
                'occurred_on' => $occurredOn,
                'category' => $category !== '' ? $category : null,
                'description' => $description !== '' ? $description : null,
            ],
        ]);

        return Response::redirect($tenant->urlPrefix() . '/finance?message=Salvo');
    }
}
