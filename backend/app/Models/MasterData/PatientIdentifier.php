<?php

declare(strict_types=1);

namespace App\Models\MasterData;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * PatientIdentifier stores a patient identity value (e.g. an MRN, SSN, or
 * national ID) typed by an IdentityType. Multiple identifiers can exist per
 * patient.
 */
final class PatientIdentifier extends BaseModel
{
    use HasUuids;
    use SoftDeletes;

    protected $table = 'patient_identifier';

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

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function identityType(): BelongsTo
    {
        return $this->belongsTo(IdentityType::class, 'identity_type_id');
    }
}
