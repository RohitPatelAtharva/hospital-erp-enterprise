<?php

declare(strict_types=1);

namespace App\Models\MasterData;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A type of relationship between patients or organizations.
 *
 * @property string $code
 */
final class RelationType extends BaseModel
{
    use HasUuids;
    use SoftDeletes;

    protected $table = 'relation_type';

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

    public function patientRelations(): HasMany
    {
        return $this->hasMany(PatientRelation::class, 'relation_type_id');
    }

    public function organizationRelationships(): HasMany
    {
        return $this->hasMany(OrganizationRelationship::class, 'relation_type_id');
    }
}
