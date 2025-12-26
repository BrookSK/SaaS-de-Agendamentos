<?php

declare(strict_types=1);

namespace App\Core;

use App\Models\TenantSubscription;

final class App
{
    public function run(): void
    {
        $this->startSession();

        $router = new Router();
        Routes::register($router);

        $request = Request::fromGlobals();

        $tenant = Tenant::fromRequestPath($request->path());
        Tenant::setCurrent($tenant);

        if ($tenant !== null) {
            $request = $request->withPath($tenant->strippedPath);
        }

        if ($tenant !== null && $tenant->tenantId !== null) {
            $blocked = $this->isTenantBlocked($tenant->tenantId, $request->path());
            if ($blocked) {
                $response = Response::html('Conta bloqueada por inadimplência. Contate o suporte.', 403);
                $response->send();
                return;
            }
        }

        $response = $router->dispatch($request);
        $response->send();
    }

    private function isTenantBlocked(int $tenantId, string $path): bool
    {
        $allow = ['/', '/login', '/logout', '/book', '/forgot-password', '/reset-password', '/change-password'];
        foreach ($allow as $p) {
            if ($path === $p) {
                return false;
            }
        }

        $sub = TenantSubscription::latestByTenant($tenantId);
        if (!is_array($sub)) {
            return false;
        }

        $status = (string)($sub['status'] ?? '');
        return in_array($status, ['blocked', 'past_due'], true);
    }

    private function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
}
