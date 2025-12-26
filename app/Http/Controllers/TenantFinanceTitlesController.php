<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Core\Auth;
use App\Core\Audit;
use App\Core\Request;
use App\Core\Response;
use App\Core\Tenant;
use App\Core\WebhookDispatcher;
use App\Models\FinancialTitle;

final class TenantFinanceTitlesController extends Controller
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
        $type = (string)$request->query('type', '');
        $status = (string)$request->query('status', '');

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $from) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $to)) {
            $from = date('Y-m-01');
            $to = date('Y-m-t');
        }

        $type = $type !== '' ? $type : null;
        $status = $status !== '' ? $status : null;

        return $this->view('tenant/finance/titles', [
            'tenant' => $tenant,
            'from' => $from,
            'to' => $to,
            'type' => $type,
            'status' => $status,
            'totals' => FinancialTitle::openTotals($tenant->tenantId, $from, $to),
            'items' => FinancialTitle::listByPeriod($tenant->tenantId, $from, $to, $type, $status),
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

        $type = (string)$request->input('type', 'receivable');
        $amount = (int)$request->input('amount_cents', 0);
        $dueOn = (string)$request->input('due_on', date('Y-m-d'));
        $category = trim((string)$request->input('category', ''));
        $description = trim((string)$request->input('description', ''));

        if (!in_array($type, ['payable', 'receivable'], true) || $amount <= 0 || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dueOn)) {
            return Response::redirect($tenant->urlPrefix() . '/finance/titles?error=' . rawurlencode('Dados inválidos'));
        }

        FinancialTitle::create(
            $tenant->tenantId,
            $type,
            $amount,
            $dueOn,
            $category !== '' ? $category : null,
            $description !== '' ? $description : null
        );

        Audit::log('finance.title.create', 'financial_title', null, [
            'type' => $type,
            'amount_cents' => $amount,
            'due_on' => $dueOn,
        ]);

        WebhookDispatcher::dispatch('finance.title.created', [
            'tenant' => [
                'id' => $tenant->tenantId,
                'slug' => $tenant->slug,
            ],
            'title' => [
                'type' => $type,
                'amount_cents' => $amount,
                'due_on' => $dueOn,
                'category' => $category !== '' ? $category : null,
                'description' => $description !== '' ? $description : null,
                'status' => 'open',
            ],
        ]);

        return Response::redirect($tenant->urlPrefix() . '/finance/titles?message=Salvo');
    }

    /** @param array<string,string> $params */
    public function pay(Request $request, array $params): Response
    {
        $tenant = Tenant::current();
        if ($tenant === null || $tenant->tenantId === null) {
            return Response::html('Tenant inválido', 400);
        }

        if ($resp = Auth::requireRole('tenant_admin', 'employee')) {
            return $resp;
        }

        $id = (int)$request->input('id', 0);
        if ($id <= 0) {
            return Response::redirect($tenant->urlPrefix() . '/finance/titles');
        }

        FinancialTitle::markPaid($tenant->tenantId, $id);
        Audit::log('finance.title.pay', 'financial_title', $id, null);

        WebhookDispatcher::dispatch('finance.title.paid', [
            'tenant' => [
                'id' => $tenant->tenantId,
                'slug' => $tenant->slug,
            ],
            'title' => [
                'id' => $id,
                'status' => 'paid',
            ],
        ]);
        return Response::redirect($tenant->urlPrefix() . '/finance/titles?message=Baixado');
    }

    /** @param array<string,string> $params */
    public function cancel(Request $request, array $params): Response
    {
        $tenant = Tenant::current();
        if ($tenant === null || $tenant->tenantId === null) {
            return Response::html('Tenant inválido', 400);
        }

        if ($resp = Auth::requireRole('tenant_admin', 'employee')) {
            return $resp;
        }

        $id = (int)$request->input('id', 0);
        if ($id <= 0) {
            return Response::redirect($tenant->urlPrefix() . '/finance/titles');
        }

        FinancialTitle::cancel($tenant->tenantId, $id);
        Audit::log('finance.title.cancel', 'financial_title', $id, null);

        WebhookDispatcher::dispatch('finance.title.canceled', [
            'tenant' => [
                'id' => $tenant->tenantId,
                'slug' => $tenant->slug,
            ],
            'title' => [
                'id' => $id,
                'status' => 'canceled',
            ],
        ]);
        return Response::redirect($tenant->urlPrefix() . '/finance/titles?message=Cancelado');
    }
}
