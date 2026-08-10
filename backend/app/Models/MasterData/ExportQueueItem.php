<?php

declare(strict_types=1);

namespace App\Models\MasterData;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Append-only queue item belonging to an export batch.
 */
final class ExportQueueItem extends BaseModel
{
    use HasUuids;

    protected $table = 'export_queue_item';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'id' => 'string',
            'status' => 'string',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function exportBatch(): BelongsTo
    {
        return $this->belongsTo(ExportBatch::class, 'export_batch_id');
    }
}
