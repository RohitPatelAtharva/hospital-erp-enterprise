<?php

declare(strict_types=1);

namespace App\Models\MasterData;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Append-only batch of an inbound data import.
 */
final class ImportBatch extends BaseModel
{
    use HasUuids;

    protected $table = 'import_batch';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'id' => 'string',
            'status' => 'string',
            'actor_id' => 'string',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function stagingRows(): HasMany
    {
        return $this->hasMany(ImportStagingRow::class, 'import_batch_id');
    }
}
