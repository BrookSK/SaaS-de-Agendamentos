<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Core\Auth;
use App\Core\AsaasClient;
use App\Core\Request;
use App\Core\Response;
use App\Models\AsaasSetting;
use App\Models\Plan;
use App\Models\Tenant;
use App\Models\TenantSubscription;

final class SuperAdminSubscriptionsController extends Controller
{
    /** @param array<string,string> $params */
    public function index(Request $request, array $params): Response
    {
        if ($resp = Auth::requireRole('super_admin')) {
            return $resp;
        }

        return $this->view('super/subscriptions/index', [
            'subscriptions' => TenantSubscription::listAll(),
            'tenants' => Tenant::all(),
            'plans' => Plan::all(),
            'message' => $request->query('message'),
            'error' => $request->query('error'),
        ]);
    }

    /** @param array<string,string> $params */
    public function store(Request $request, array $params): Response
    {
        if ($resp = Auth::requireRole('super_admin')) {
            return $resp;
        }

        $tenantId = (int)$request->input('tenant_id', 0);
        $planId = (int)$request->input('plan_id', 0);
        $status = (string)$request->input('status', 'trial');

        if (!in_array($status, ['active', 'canceled', 'past_due', 'blocked', 'trial'], true)) {
            $status = 'trial';
        }

        if ($tenantId <= 0 || $planId <= 0) {
            return Response::redirect('/super/subscriptions');
        }

        TenantSubscription::upsertForTenant($tenantId, $planId, $status);

        $sub = TenantSubscription::latestByTenant($tenantId);
        if (!is_array($sub)) {
            return Response::redirect('/super/subscriptions?message=Salvo');
        }

        $settings = AsaasSetting::get();
        $apiKey = AsaasSetting::currentApiKey();
        $env = (string)($settings['environment'] ?? 'sandbox');

        if ($apiKey === null || $apiKey === '') {
            return Response::redirect('/super/subscriptions?message=Salvo');
        }

        $tenant = Tenant::findById($tenantId);
        $plan = Plan::findById($planId);
        if ($tenant === null || $plan === null) {
            return Response::redirect('/super/subscriptions?message=Salvo');
        }

        $email = $tenant->email;
        $cpfCnpj = $tenant->cpfCnpj;
        if ($email === null || $email === '' || $cpfCnpj === null || $cpfCnpj === '') {
            return Response::redirect('/super/subscriptions?error=' . rawurlencode('Tenant precisa de email e CPF/CNPJ para criar Customer no ASAAS. Atualize em /super/tenants.'));
        }

        $asaasCustomerId = isset($sub['asaas_customer_id']) ? (string)($sub['asaas_customer_id'] ?? '') : '';
        $asaasSubscriptionId = isset($sub['asaas_subscription_id']) ? (string)($sub['asaas_subscription_id'] ?? '') : '';

        if ($asaasSubscriptionId !== '') {
            return Response::redirect('/super/subscriptions?message=Salvo');
        }

        try {
            if (!function_exists('curl_init')) {
                throw new \RuntimeException('PHP sem extensão cURL habilitada');
            }

            $client = new AsaasClient($apiKey, $env);

            if ($asaasCustomerId === '') {
                $customer = $client->createCustomer(
                    $tenant->name,
                    $email,
                    $tenant->phone,
                    $cpfCnpj
                );

                $asaasCustomerId = (string)($customer['id'] ?? '');
                if ($asaasCustomerId === '') {
                    throw new \RuntimeException('ASAAS não retornou customer id');
                }
            }

            $cycle = $this->mapBillingCycleToAsaasCycle($plan->billingCycle);
            $value = round($plan->priceCents / 100, 2);
            $billingType = 'BOLETO';

            $subResp = $client->createSubscription(
                $asaasCustomerId,
                $billingType,
                $value,
                $cycle,
                'Plano: ' . $plan->name,
                'tenant:' . $tenant->id
            );

            $asaasSubscriptionId = (string)($subResp['id'] ?? '');
            if ($asaasSubscriptionId === '') {
                throw new \RuntimeException('ASAAS não retornou subscription id');
            }

            TenantSubscription::setAsaasLink((int)$sub['id'], $asaasCustomerId, $asaasSubscriptionId);
        } catch (\Throwable $e) {
            return Response::redirect('/super/subscriptions?error=' . rawurlencode('ASAAS: ' . $e->getMessage()));
        }

        return Response::redirect('/super/subscriptions?message=Assinatura%20ASAAS%20criada');
    }

    private function mapBillingCycleToAsaasCycle(string $billingCycle): string
    {
        return match ($billingCycle) {
            'annual' => 'YEARLY',
            'semiannual' => 'SEMIANNUAL',
            default => 'MONTHLY',
        };
    }
}
