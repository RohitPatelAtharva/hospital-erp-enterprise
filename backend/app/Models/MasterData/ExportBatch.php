<?php

declare(strict_types=1);

namespace App\Models\MasterData;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Append-only batch of an outbound data export.
 */
final class ExportBatch extends BaseModel
{
    use HasUuids;

    protected $table = 'export_batch';

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

    public function queueItems(): HasMany
    {
        return $this->hasMany(ExportQueueItem::class, 'export_batch_id');
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(ExportRecipient::class, 'export_batch_id');
    }
}
