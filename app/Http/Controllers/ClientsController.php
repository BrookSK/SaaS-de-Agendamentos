<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Core\Auth;
use App\Core\Audit;
use App\Core\WebhookDispatcher;
use App\Core\Request;
use App\Core\Response;
use App\Core\Tenant;
use App\Models\Client;

final class ClientsController extends Controller
{
    /** @param array<string,string> $params */
    public function index(Request $request, array $params): Response
    {
        $tenant = Tenant::current();
        if ($tenant === null || $tenant->tenantId === null) {
            return Response::html('Tenant inválido. Acesse via /t/{slug}/clients', 400);
        }

        if ($resp = Auth::requireRole('tenant_admin', 'employee')) {
            return $resp;
        }

        $clients = Client::allByTenant($tenant->tenantId);

        return $this->view('tenant/clients/index', [
            'tenant' => $tenant,
            'clients' => $clients,
        ]);
    }

    /** @param array<string,string> $params */
    public function store(Request $request, array $params): Response
    {
        $tenant = Tenant::current();
        if ($tenant === null || $tenant->tenantId === null) {
            return Response::html('Tenant inválido.', 400);
        }

        if ($resp = Auth::requireRole('tenant_admin', 'employee')) {
            return $resp;
        }

        $name = trim((string)$request->input('name', ''));
        $phone = trim((string)$request->input('phone', ''));
        $email = trim((string)$request->input('email', ''));

        if ($name === '') {
            return Response::redirect($tenant->urlPrefix() . '/clients');
        }

        Client::create(
            $tenant->tenantId,
            $name,
            $phone !== '' ? $phone : null,
            $email !== '' ? $email : null
        );

        Audit::log('client.create', 'client', null, [
            'name' => $name,
        ]);

        WebhookDispatcher::dispatch('client.created', [
            'tenant' => [
                'id' => $tenant->tenantId,
                'slug' => $tenant->slug,
            ],
            'client' => [
                'name' => $name,
                'phone' => $phone !== '' ? $phone : null,
                'email' => $email !== '' ? $email : null,
            ],
        ]);

        return Response::redirect($tenant->urlPrefix() . '/clients');
    }
}
