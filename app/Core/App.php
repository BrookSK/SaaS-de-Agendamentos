<?php

declare(strict_types=1);

namespace App\Core;

use App\Models\TenantSubscription;

final class App
{
    public function run(): void
    {
        $this->loadEnv();
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

    private function loadEnv(): void
    {
        $envPath = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . '.env';
        if (!is_file($envPath)) {
            return;
        }

        $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return;
        }

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            $pos = strpos($line, '=');
            if ($pos === false) {
                continue;
            }

            $key = trim(substr($line, 0, $pos));
            $value = trim(substr($line, $pos + 1));
            $value = trim($value, " \t\n\r\0\x0B\"'");

            if ($key !== '' && getenv($key) === false) {
                putenv($key . '=' . $value);
                $_ENV[$key] = $value;
            }
        }
    }
}
