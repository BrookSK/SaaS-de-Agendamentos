<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Core\Auth;
use App\Core\Audit;
use App\Core\WebhookDispatcher;
use App\Core\Request;
use App\Core\Response;
use App\Core\Tenant;
use App\Models\Service;

final class ServicesController extends Controller
{
    /** @param array<string,string> $params */
    public function index(Request $request, array $params): Response
    {
        $tenant = Tenant::current();
        if ($tenant === null || $tenant->tenantId === null) {
            return Response::html('Tenant inválido. Acesse via /t/{slug}/services', 400);
        }

        if ($resp = Auth::requireRole('tenant_admin', 'employee')) {
            return $resp;
        }

        $services = Service::allByTenant($tenant->tenantId);

        return $this->view('tenant/services/index', [
            'tenant' => $tenant,
            'services' => $services,
        ]);
    }

    /** @param array<string,string> $params */
    public function store(Request $request, array $params): Response
    {
        $tenant = Tenant::current();
        if ($tenant === null || $tenant->tenantId === null) {
            return Response::html('Tenant inválido.', 400);
        }

        if ($resp = Auth::requireRole('tenant_admin')) {
            return $resp;
        }

        $name = trim((string)$request->input('name', ''));
        $duration = (int)$request->input('duration_minutes', 0);
        $price = (int)$request->input('price_cents', 0);

        if ($name === '' || $duration <= 0 || $price < 0) {
            return Response::redirect($tenant->urlPrefix() . '/services');
        }

        Service::create($tenant->tenantId, $name, $duration, $price);

        Audit::log('service.create', 'service', null, [
            'name' => $name,
            'duration_minutes' => $duration,
            'price_cents' => $price,
        ]);

        WebhookDispatcher::dispatch('service.created', [
            'tenant' => [
                'id' => $tenant->tenantId,
                'slug' => $tenant->slug,
            ],
            'service' => [
                'name' => $name,
                'duration_minutes' => $duration,
                'price_cents' => $price,
            ],
        ]);

        return Response::redirect($tenant->urlPrefix() . '/services');
    }
}
