<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Tenancy\TenantContext;
use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Establishes the tenant context for an authenticated request.
 *
 * The tenant/facility scope is derived from the authenticated principal's own
 * scope (per docs/09-MULTI-TENANCY.md and docs/07-ROLES-PERMISSIONS.md), never
 * from a client-supplied header. A principal without an assigned facility scope
 * is denied (least privilege / default-deny).
 */
final class SetTenantContext
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null) {
            // Unauthenticated request (e.g. public endpoints): no tenant context.
            TenantContext::clear();

            return $next($request);
        }

        $tenantId = $this->principalTenantId($user);
        $facilityId = $this->principalFacilityId($user);

        if ($tenantId === null || $facilityId === null) {
            // No tenant/facility scope -> deny. Note: do not call Auth::logout()
            // here; the Sanctum RequestGuard has no logout() method and it would
            // raise "Method RequestGuard::logout does not exist". The exception
            // alone rejects the request (403) and the request guard is recreated
            // per request anyway.
            throw new AuthorizationException('Principal has no assigned tenant/facility scope.');
        }

        TenantContext::setContext($tenantId, $facilityId);

        return $next($request);
    }

    public function terminate(Request $request, Response $response): void
    {
        TenantContext::clear();
    }

    private function principalTenantId(mixed $user): ?string
    {
        return $user->tenant_id ?? null;
    }

    private function principalFacilityId(mixed $user): ?string
    {
        return $user->facility_id ?? null;
    }
}
