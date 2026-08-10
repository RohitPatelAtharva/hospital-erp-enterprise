<?php

declare(strict_types=1);

namespace App\Models\MasterData;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * PatientConsent records a consent granted by a patient, typed by a
 * ConsentType. A patient may hold multiple consents.
 */
final class PatientConsent extends BaseModel
{
    use HasUuids;
    use SoftDeletes;

    protected $table = 'patient_consent';

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

    public function consentType(): BelongsTo
    {
        return $this->belongsTo(ConsentType::class, 'consent_type_id');
    }
}
