<?php

declare(strict_types=1);

namespace App\Models\MasterData;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

final class ClinicalMapping extends BaseModel
{
    use HasUuids;
    use SoftDeletes;

    protected $table = 'clinical_mapping';

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

    public function sourceCode(): BelongsTo
    {
        return $this->belongsTo(ClinicalCode::class, 'source_code_id');
    }

    public function targetCode(): BelongsTo
    {
        return $this->belongsTo(ClinicalCode::class, 'target_code_id');
    }
}
