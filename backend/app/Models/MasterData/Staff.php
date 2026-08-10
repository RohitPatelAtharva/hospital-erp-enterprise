<?php

declare(strict_types=1);

namespace App\Models\MasterData;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A staff member record in the master data module.
 */
final class Staff extends BaseModel
{
    use HasUuids;
    use SoftDeletes;

    protected $table = 'staff';

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

    public function enterprisePerson(): BelongsTo
    {
        return $this->belongsTo(EnterprisePerson::class, 'enterprise_person_id');
    }

    public function masterRecord(): BelongsTo
    {
        return $this->belongsTo(MasterRecord::class, 'master_record_id');
    }

    public function staffIdentifiers(): HasMany
    {
        return $this->hasMany(StaffIdentifier::class, 'staff_id');
    }

    public function staffDemographics(): HasMany
    {
        return $this->hasMany(StaffDemographic::class, 'staff_id');
    }

    public function staffConsents(): HasMany
    {
        return $this->hasMany(StaffConsent::class, 'staff_id');
    }

    public function stewardAssignments(): HasMany
    {
        return $this->hasMany(StewardAssignment::class, 'staff_id');
    }
}
