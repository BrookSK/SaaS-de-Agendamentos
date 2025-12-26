<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\Tenant;
use App\Models\User;

final class AuthController extends Controller
{
    /** @param array<string,string> $params */
    public function showLogin(Request $request, array $params): Response
    {
        $tenant = Tenant::current();

        return $this->view('auth/login', [
            'tenant' => $tenant,
            'error' => $request->query('error'),
        ]);
    }

    /** @param array<string,string> $params */
    public function login(Request $request, array $params): Response
    {
        $email = (string)$request->input('email', '');
        $password = (string)$request->input('password', '');

        $tenant = Tenant::current();
        $tenantId = $tenant?->tenantId;

        if ($tenant !== null && $tenantId === null) {
            return Response::html('Tenant inválido ou banco não configurado.', 400);
        }

        $user = User::findByEmail($email, $tenantId);
        if ($user === null || !password_verify($password, $user->passwordHash)) {
            $prefix = $tenant?->urlPrefix() ?? '';
            return Response::redirect($prefix . '/login?error=Credenciais%20inv%C3%A1lidas');
        }

        Auth::login($user);

        if ($user->role === 'super_admin') {
            return Response::redirect('/super/dashboard');
        }

        $prefix = $tenant?->urlPrefix() ?? '';
        return Response::redirect($prefix . '/dashboard');
    }

    /** @param array<string,string> $params */
    public function logout(Request $request, array $params): Response
    {
        $tenant = Tenant::current();
        Auth::logout();
        $prefix = $tenant?->urlPrefix() ?? '';
        return Response::redirect($prefix . '/login');
    }
}
