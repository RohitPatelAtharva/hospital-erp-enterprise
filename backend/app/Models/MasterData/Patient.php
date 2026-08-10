<?php

declare(strict_types=1);

namespace App\Models\MasterData;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Patient master record. A patient is a master-data entity that links an
 * EnterprisePerson and a MasterRecord, carrying demographic fields such as
 * name, date of birth, and sex. It aggregates identifiers, demographics,
 * consents, relations, and aliases.
 */
final class Patient extends BaseModel
{
    use HasUuids;
    use SoftDeletes;

    protected $table = 'patient';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'id' => 'string',
            'version' => 'integer',
            'status' => 'string',
            'dob' => 'date:Y-m-d',
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

    public function identifiers(): HasMany
    {
        return $this->hasMany(PatientIdentifier::class, 'patient_id');
    }

    public function demographics(): HasMany
    {
        return $this->hasMany(PatientDemographic::class, 'patient_id');
    }

    public function consents(): HasMany
    {
        return $this->hasMany(PatientConsent::class, 'patient_id');
    }

    public function relations(): HasMany
    {
        return $this->hasMany(PatientRelation::class, 'patient_id');
    }

    public function relatedPatients(): HasMany
    {
        return $this->hasMany(PatientRelation::class, 'related_patient_id');
    }

    public function aliases(): HasMany
    {
        return $this->hasMany(PatientAlias::class, 'patient_id');
    }
}
