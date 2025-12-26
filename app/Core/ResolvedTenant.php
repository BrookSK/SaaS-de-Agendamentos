<?php

declare(strict_types=1);

namespace App\Core;

final class ResolvedTenant
{
    public function __construct(
        public string $slug,
        public string $strippedPath,
        public ?int $tenantId
    ) {}

    public function urlPrefix(): string
    {
        return '/t/' . rawurlencode($this->slug);
    }
}
