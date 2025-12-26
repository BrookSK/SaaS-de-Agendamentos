<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Models\Tenant;

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

        if ($name === '' || $slug === '' || !preg_match('/^[a-z0-9-]+$/', $slug)) {
            return Response::redirect('/super/tenants');
        }

        if (!in_array($status, ['active', 'blocked'], true)) {
            $status = 'active';
        }

        Tenant::create(
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
