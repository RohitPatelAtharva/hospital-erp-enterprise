<?php

declare(strict_types=1);

namespace App\Models\MasterData;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final class IdentityType extends BaseModel
{
    use HasUuids;
    use SoftDeletes;

    protected $table = 'identity_type';

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

    public function identityRecords(): HasMany
    {
        return $this->hasMany(IdentityRecord::class, 'identity_type_id');
    }

    public function patientIdentifiers(): HasMany
    {
        return $this->hasMany(PatientIdentifier::class, 'identity_type_id');
    }

    public function staffIdentifiers(): HasMany
    {
        return $this->hasMany(StaffIdentifier::class, 'identity_type_id');
    }

    public function providerIdentifiers(): HasMany
    {
        return $this->hasMany(ProviderIdentifier::class, 'identity_type_id');
    }

    public function organizationIdentifiers(): HasMany
    {
        return $this->hasMany(OrganizationIdentifier::class, 'identity_type_id');
    }
}
