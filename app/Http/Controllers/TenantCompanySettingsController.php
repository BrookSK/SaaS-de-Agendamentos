<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\Tenant;
use App\Models\Tenant as TenantModel;

final class TenantCompanySettingsController extends Controller
{
    /** @param array<string,string> $params */
    public function index(Request $request, array $params): Response
    {
        $tenant = Tenant::current();
        if ($tenant === null || $tenant->tenantId === null) {
            return Response::html('Tenant inválido. Acesse via /t/{slug}/settings/company', 400);
        }

        if ($resp = Auth::requireRole('tenant_admin')) {
            return $resp;
        }

        $tab = (string)$request->query('tab', 'company');
        $allowed = [
            'company',
            'general',
            'appearance',
            'business_hours',
            'holidays',
            'embed',
            'qr',
            'payments',
            'notifications',
        ];
        if (!in_array($tab, $allowed, true)) {
            $tab = 'company';
        }

        $company = TenantModel::findById($tenant->tenantId);

        return $this->view('tenant/settings/company', [
            'tenant' => $tenant,
            'company' => $company,
            'tab' => $tab,
            'message' => $request->query('message'),
            'error' => $request->query('error'),
        ]);
    }

    /** @param array<string,string> $params */
    public function store(Request $request, array $params): Response
    {
        $tenant = Tenant::current();
        if ($tenant === null || $tenant->tenantId === null) {
            return Response::html('Tenant inválido. Acesse via /t/{slug}/settings/company', 400);
        }

        if ($resp = Auth::requireRole('tenant_admin')) {
            return $resp;
        }

        $name = trim((string)$request->input('name', ''));
        $email = trim((string)$request->input('email', ''));
        $phone = trim((string)$request->input('phone', ''));
        $cpfCnpj = trim((string)$request->input('cpf_cnpj', ''));

        if ($name === '') {
            return Response::redirect($tenant->urlPrefix() . '/settings/company?error=' . rawurlencode('Informe o nome da empresa.'));
        }

        if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            return Response::redirect($tenant->urlPrefix() . '/settings/company?error=' . rawurlencode('E-mail inválido.'));
        }

        $t = TenantModel::findById($tenant->tenantId);
        if ($t === null) {
            return Response::redirect($tenant->urlPrefix() . '/settings/company?error=' . rawurlencode('Empresa não encontrada.'));
        }

        TenantModel::updateById(
            $t->id,
            $name,
            $t->slug,
            $t->status,
            $email !== '' ? $email : null,
            $phone !== '' ? $phone : null,
            $cpfCnpj !== '' ? $cpfCnpj : null
        );

        return Response::redirect($tenant->urlPrefix() . '/settings/company?message=Salvo');
    }
}
