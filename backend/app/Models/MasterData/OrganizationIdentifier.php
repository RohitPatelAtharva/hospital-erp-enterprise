<?php

declare(strict_types=1);

namespace App\Models\MasterData;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * An identifier (e.g. license, tax id) assigned to an organization.
 */
final class OrganizationIdentifier extends BaseModel
{
    use HasUuids;
    use SoftDeletes;

    protected $table = 'organization_identifier';

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

    public function identityType(): BelongsTo
    {
        return $this->belongsTo(IdentityType::class, 'identity_type_id');
    }
}
