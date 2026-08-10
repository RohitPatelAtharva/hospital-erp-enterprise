<?php

declare(strict_types=1);

namespace App\Models\MasterData;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A type of consent a patient or staff member may grant.
 *
 * @property string $code
 */
final class ConsentType extends BaseModel
{
    use HasUuids;
    use SoftDeletes;

    protected $table = 'consent_type';

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

    public function patientConsents(): HasMany
    {
        return $this->hasMany(PatientConsent::class, 'consent_type_id');
    }

    public function staffConsents(): HasMany
    {
        return $this->hasMany(StaffConsent::class, 'consent_type_id');
    }
}
