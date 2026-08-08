<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Scopes\TenantScope;
use App\Tenancy\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Base model for all tenant-scoped business records.
 *
 * - Applies the TenantScope (tenant isolation) to every query by default.
 * - Exposes `scopeTenant` for explicit scoping and `withoutTenantScope` for
 *   authorized cross-tenant/admin reads (which are audited at the call site).
 * - Master Data models (Phase 2+) extend this; infrastructure tables (e.g.
 *   `users`) intentionally do not.
 */
abstract class BaseModel extends Model
{
    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope());
    }

    public function scopeTenant(Builder $query): Builder
    {
        if (TenantContext::hasTenant()) {
            return $query->where($this->qualifyColumn('tenant_id'), TenantContext::tenantId());
        }

        return $query;
    }

    public function scopeWithoutTenantScope(Builder $query): Builder
    {
        return $query->withoutGlobalScope(TenantScope::class);
    }
}
