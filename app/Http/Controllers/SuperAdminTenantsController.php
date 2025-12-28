<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Core\Auth;
use App\Core\Db;
use App\Core\Request;
use App\Core\Response;
use App\Models\Tenant;
use App\Models\User;

final class SuperAdminTenantsController extends Controller
{
    /** @param array<string,string> $params */
    public function index(Request $request, array $params): Response
    {
        if ($resp = Auth::requireRole('super_admin')) {
            return $resp;
        }

        return $this->view('super/tenants/index', [
            'tenants' => Tenant::all(),
        ]);
    }

    /** @param array<string,string> $params */
    public function store(Request $request, array $params): Response
    {
        if ($resp = Auth::requireRole('super_admin')) {
            return $resp;
        }

        $name = trim((string)$request->input('name', ''));
        $slug = trim((string)$request->input('slug', ''));
        $status = (string)$request->input('status', 'active');
        $email = trim((string)$request->input('email', ''));
        $phone = trim((string)$request->input('phone', ''));
        $cpfCnpj = trim((string)$request->input('cpf_cnpj', ''));

        $adminEmail = trim((string)$request->input('admin_email', ''));
        $adminPassword = (string)$request->input('admin_password', '');

        if ($slug === '' && $name !== '') {
            $slug = mb_strtolower($name);
            $slug = preg_replace('/[^a-z0-9]+/u', '-', $slug) ?? $slug;
            $slug = trim($slug, '-');
            $slug = preg_replace('/-+/', '-', $slug) ?? $slug;
        }

        if ($name === '' || $slug === '' || !preg_match('/^[a-z0-9-]+$/', $slug)) {
            return Response::redirect('/super/tenants');
        }

        if ($adminEmail === '' || !filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
            return Response::redirect('/super/tenants');
        }

        if (mb_strlen($adminPassword) < 6) {
            return Response::redirect('/super/tenants');
        }

        if (!in_array($status, ['active', 'blocked'], true)) {
            $status = 'active';
        }

        $pdo = Db::pdo();

        try {
            $pdo->beginTransaction();

            $tenantId = Tenant::createReturningId(
                $name,
                $slug,
                $status,
                $email !== '' ? $email : null,
                $phone !== '' ? $phone : null,
                $cpfCnpj !== '' ? $cpfCnpj : null
            );

            if (User::findByEmail($adminEmail, $tenantId) !== null) {
                $pdo->rollBack();
                return Response::redirect('/super/tenants');
            }

            $adminName = 'Admin ' . $name;
            $hash = password_hash($adminPassword, PASSWORD_BCRYPT);
            User::createTenantAdmin($tenantId, $adminName, $adminEmail, $hash, 'active');

            $pdo->commit();
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            return Response::redirect('/super/tenants');
        }

        return Response::redirect('/super/tenants');
    }

    /** @param array<string,string> $params */
    public function edit(Request $request, array $params): Response
    {
        if ($resp = Auth::requireRole('super_admin')) {
            return $resp;
        }

        $id = (int)($params['id'] ?? 0);
        if ($id <= 0) {
            return Response::redirect('/super/tenants');
        }

        $tenant = Tenant::findById($id);
        if ($tenant === null) {
            return Response::redirect('/super/tenants');
        }

        return $this->view('super/tenants/edit', [
            'tenant' => $tenant,
        ]);
    }

    /** @param array<string,string> $params */
    public function update(Request $request, array $params): Response
    {
        if ($resp = Auth::requireRole('super_admin')) {
            return $resp;
        }

        $id = (int)($params['id'] ?? 0);
        if ($id <= 0) {
            return Response::redirect('/super/tenants');
        }

        $name = trim((string)$request->input('name', ''));
        $slug = trim((string)$request->input('slug', ''));
        $status = (string)$request->input('status', 'active');
        $email = trim((string)$request->input('email', ''));
        $phone = trim((string)$request->input('phone', ''));
        $cpfCnpj = trim((string)$request->input('cpf_cnpj', ''));

        if ($name === '' || $slug === '' || !preg_match('/^[a-z0-9-]+$/', $slug)) {
            return Response::redirect('/super/tenants/' . $id . '/edit');
        }

        if (!in_array($status, ['active', 'blocked'], true)) {
            $status = 'active';
        }

        Tenant::updateById(
            $id,
            $name,
            $slug,
            $status,
            $email !== '' ? $email : null,
            $phone !== '' ? $phone : null,
            $cpfCnpj !== '' ? $cpfCnpj : null
        );

        return Response::redirect('/super/tenants');
    }
}
