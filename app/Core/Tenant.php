<?php

declare(strict_types=1);

namespace App\Core;

use App\Models\Tenant as TenantModel;

final class Tenant
{
    private static ?ResolvedTenant $current = null;

    public static function setCurrent(?ResolvedTenant $tenant): void
    {
        self::$current = $tenant;
    }

    public static function current(): ?ResolvedTenant
    {
        return self::$current;
    }

    public static function fromRequestPath(string $path): ?ResolvedTenant
    {
        if (!preg_match('#^/t/([^/]+)(/.*)?$#', $path, $m)) {
            return null;
        }

        $slug = $m[1];
        $rest = $m[2] ?? '';
        $stripped = $rest === '' ? '/' : $rest;
        $stripped = rtrim($stripped, '/') === '' ? '/' : rtrim($stripped, '/');

        $tenantId = null;
        try {
            $tenant = TenantModel::findBySlug($slug);
            $tenantId = $tenant?->id;
        } catch (\Throwable $e) {
            $tenantId = null;
        }

        return new ResolvedTenant($slug, $stripped, $tenantId);
    }
}
