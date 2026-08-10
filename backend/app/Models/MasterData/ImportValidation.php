<?php

declare(strict_types=1);

namespace App\Models\MasterData;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Append-only validation result for a single import staging row.
 */
final class ImportValidation extends BaseModel
{
    use HasUuids;

    protected $table = 'import_validation';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'id' => 'string',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function stagingRow(): BelongsTo
    {
        return $this->belongsTo(ImportStagingRow::class, 'import_staging_row_id');
    }
}
