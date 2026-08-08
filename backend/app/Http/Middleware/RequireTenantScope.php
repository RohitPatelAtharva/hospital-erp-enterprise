<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Tenancy\TenantContext;
use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Guards tenant-scoped endpoints.
 *
 * For any route that must operate inside a tenant boundary, this middleware
 * verifies a tenant context has been established (by SetTenantContext). It is
 * applied to business routes so a missing tenant context cannot silently leak
 * into unscoped queries.
 */
final class RequireTenantScope
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! TenantContext::hasTenant()) {
            throw new AuthorizationException('Tenant context is required for this resource.');
        }

        return $next($request);
    }
}
