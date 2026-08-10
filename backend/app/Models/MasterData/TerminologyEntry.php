<?php

declare(strict_types=1);

namespace App\Models\MasterData;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

final class TerminologyEntry extends BaseModel
{
    use HasUuids;
    use SoftDeletes;

    protected $table = 'terminology_entry';

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

    public function edition(): BelongsTo
    {
        return $this->belongsTo(TerminologyEdition::class, 'terminology_edition_id');
    }

    public function vocabulary(): BelongsTo
    {
        return $this->belongsTo(ClinicalVocabulary::class, 'clinical_vocabulary_id');
    }
}
