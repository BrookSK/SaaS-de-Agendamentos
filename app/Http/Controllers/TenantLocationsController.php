<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\Tenant;
use App\Models\Location;

final class TenantLocationsController extends Controller
{
    /** @param array<string,string> $params */
    public function index(Request $request, array $params): Response
    {
        $tenant = Tenant::current();
        if ($tenant === null || $tenant->tenantId === null) {
            return Response::html('Tenant inválido. Acesse via /t/{slug}/locations', 400);
        }

        if ($resp = Auth::requireRole('tenant_admin')) {
            return $resp;
        }

        return $this->view('tenant/locations/index', [
            'tenant' => $tenant,
            'locations' => Location::allByTenant($tenant->tenantId),
            'message' => $request->query('message'),
            'error' => $request->query('error'),
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
        $address = trim((string)$request->input('address', ''));
        $phone = trim((string)$request->input('phone', ''));

        if ($name === '') {
            return Response::redirect($tenant->urlPrefix() . '/locations?error=' . rawurlencode('Informe um nome.'));
        }

        Location::create(
            $tenant->tenantId,
            $name,
            $address !== '' ? $address : null,
            $phone !== '' ? $phone : null
        );

        return Response::redirect($tenant->urlPrefix() . '/locations?message=Salvo');
    }

    /** @param array<string,string> $params */
    public function delete(Request $request, array $params): Response
    {
        $tenant = Tenant::current();
        if ($tenant === null || $tenant->tenantId === null) {
            return Response::html('Tenant inválido.', 400);
        }

        if ($resp = Auth::requireRole('tenant_admin')) {
            return $resp;
        }

        $id = (int)$request->input('id', 0);
        if ($id > 0) {
            Location::deleteById($tenant->tenantId, $id);
        }

        return Response::redirect($tenant->urlPrefix() . '/locations?message=Removido');
    }
}
