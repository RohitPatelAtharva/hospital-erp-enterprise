<?php

declare(strict_types=1);

namespace App\Models\MasterData;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A typed relationship between two organizations.
 */
final class OrganizationRelationship extends BaseModel
{
    use HasUuids;
    use SoftDeletes;

    protected $table = 'organization_relationship';

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

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'organization_id');
    }

    public function relatedOrganization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'related_org_id');
    }

    public function relationType(): BelongsTo
    {
        return $this->belongsTo(RelationType::class, 'relation_type_id');
    }
}
