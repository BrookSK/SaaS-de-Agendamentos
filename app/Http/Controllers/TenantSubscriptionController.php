<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\Tenant;
use App\Models\Plan;
use App\Models\TenantSubscription;
use App\Models\TenantSubscriptionPayment;

final class TenantSubscriptionController extends Controller
{
    /** @param array<string,string> $params */
    public function index(Request $request, array $params): Response
    {
        $tenant = Tenant::current();
        if ($tenant === null || $tenant->tenantId === null) {
            return Response::html('Tenant inválido. Acesse via /t/{slug}/subscription', 400);
        }

        if ($resp = Auth::requireRole('tenant_admin')) {
            return $resp;
        }

        $sub = TenantSubscription::latestByTenant($tenant->tenantId);
        $plan = null;
        if (is_array($sub) && isset($sub['plan_id'])) {
            $plan = Plan::findById((int)$sub['plan_id']);
        }

        $plans = array_values(array_filter(Plan::all(), static fn (Plan $p) => $p->active === 1));

        return $this->view('tenant/subscription/index', [
            'tenant' => $tenant,
            'subscription' => $sub,
            'currentPlan' => $plan,
            'plans' => $plans,
        ]);
    }

    /** @param array<string,string> $params */
    public function changePlan(Request $request, array $params): Response
    {
        $tenant = Tenant::current();
        if ($tenant === null || $tenant->tenantId === null) {
            return Response::html('Tenant inválido.', 400);
        }

        if ($resp = Auth::requireRole('tenant_admin')) {
            return $resp;
        }

        $planId = (int)$request->input('plan_id', 0);
        $plan = $planId > 0 ? Plan::findById($planId) : null;
        if ($plan === null || $plan->active !== 1) {
            return Response::redirect($tenant->urlPrefix() . '/subscription');
        }

        TenantSubscription::upsertForTenant($tenant->tenantId, $plan->id, 'active');

        $sub = TenantSubscription::latestByTenant($tenant->tenantId);
        if (is_array($sub) && isset($sub['id']) && $plan->priceCents > 0) {
            TenantSubscriptionPayment::createManual((int)$sub['id'], (int)$plan->priceCents, 'pending');
        }

        return Response::redirect($tenant->urlPrefix() . '/subscription?message=' . rawurlencode('Plano atualizado'));
    }

    /** @param array<string,string> $params */
    public function invoices(Request $request, array $params): Response
    {
        $tenant = Tenant::current();
        if ($tenant === null || $tenant->tenantId === null) {
            return Response::html('Tenant inválido.', 400);
        }

        if ($resp = Auth::requireRole('tenant_admin')) {
            return $resp;
        }

        $sub = TenantSubscription::latestByTenant($tenant->tenantId);
        $payments = [];
        if (is_array($sub) && isset($sub['id'])) {
            $payments = TenantSubscriptionPayment::listBySubscription((int)$sub['id']);
        }

        return $this->view('tenant/subscription/invoices', [
            'tenant' => $tenant,
            'subscription' => $sub,
            'payments' => $payments,
        ]);
    }
}
