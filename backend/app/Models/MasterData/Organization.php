<?php

declare(strict_types=1);

namespace App\Models\MasterData;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A business organization (tenant-scoped master data entity).
 */
final class Organization extends BaseModel
{
    use HasUuids;
    use SoftDeletes;

    protected $table = 'organization';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'id' => 'string',
            'version' => 'integer',
            'status' => 'string',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function masterRecord(): BelongsTo
    {
        return $this->belongsTo(MasterRecord::class, 'master_record_id');
    }

    public function organizationType(): BelongsTo
    {
        return $this->belongsTo(OrganizationType::class, 'organization_type_id');
    }

    public function identifiers(): HasMany
    {
        return $this->hasMany(OrganizationIdentifier::class, 'organization_id');
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(OrganizationContact::class, 'organization_id');
    }

    public function relationships(): HasMany
    {
        return $this->hasMany(OrganizationRelationship::class, 'organization_id');
    }

    public function relatedRelationships(): HasMany
    {
        return $this->hasMany(OrganizationRelationship::class, 'related_org_id');
    }

    public function identityIssuers(): HasMany
    {
        return $this->hasMany(IdentityIssuer::class, 'organization_id');
    }

    public function networks(): HasMany
    {
        return $this->hasMany(ProviderNetwork::class, 'network_id');
    }
}
