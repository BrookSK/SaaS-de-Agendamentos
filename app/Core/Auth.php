<?php

declare(strict_types=1);

namespace App\Core;

use App\Models\User;

final class Auth
{
    public static function user(): ?array
    {
        return $_SESSION['auth_user'] ?? null;
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function login(User $user): void
    {
        $_SESSION['auth_user'] = [
            'id' => $user->id,
            'tenant_id' => $user->tenantId,
            'email' => $user->email,
            'name' => $user->name,
            'role' => $user->role,
        ];
    }

    public static function logout(): void
    {
        unset($_SESSION['auth_user']);
    }

    public static function requireLogin(): ?Response
    {
        if (!self::check()) {
            $prefix = Tenant::current()?->urlPrefix() ?? '';
            return Response::redirect($prefix . '/login');
        }
        return null;
    }

    public static function requireRole(string ...$roles): ?Response
    {
        $u = self::user();
        if ($u === null) {
            return self::requireLogin();
        }

        if (!in_array($u['role'] ?? null, $roles, true)) {
            return Response::html('403 - Acesso negado', 403);
        }

        return null;
    }
}
