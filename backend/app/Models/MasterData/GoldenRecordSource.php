<?php

declare(strict_types=1);

namespace App\Models\MasterData;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Tracks the source system a golden record link originates from.
 */
final class GoldenRecordSource extends BaseModel
{
    use HasUuids;
    use SoftDeletes;

    protected $table = 'golden_record_source';

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

    public function goldenRecordLink(): BelongsTo
    {
        return $this->belongsTo(GoldenRecordLink::class, 'golden_record_link_id');
    }
}
