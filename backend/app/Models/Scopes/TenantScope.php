<?php

declare(strict_types=1);

namespace App\Models\Scopes;

use App\Tenancy\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Enforces tenant isolation at the query level (defense in depth).
 *
 * When a tenant context is active, every query on a tenant-scoped model is
 * filtered by the current tenant_id. Authorized cross-tenant/admin reads must
 * opt out explicitly (withoutGlobalScope), keeping cross-tenant access both
 * deliberate and audited.
 */
final class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if (! TenantContext::hasTenant()) {
            return;
        }

        $builder->where($model->qualifyColumn('tenant_id'), TenantContext::tenantId());
    }
}
