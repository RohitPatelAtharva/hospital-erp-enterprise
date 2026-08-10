<?php

declare(strict_types=1);

namespace App\Models\MasterData;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * PatientRelation links one patient to another as a relative, typed by a
 * RelationType. Each row connects a patient (patient_id) with a related
 * patient (related_patient_id).
 */
final class PatientRelation extends BaseModel
{
    use HasUuids;
    use SoftDeletes;

    protected $table = 'patient_relation';

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

    public function relatedPatient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'related_patient_id');
    }

    public function relationType(): BelongsTo
    {
        return $this->belongsTo(RelationType::class, 'relation_type_id');
    }
}
