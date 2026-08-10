<?php

declare(strict_types=1);

namespace App\Models\MasterData;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Append-only record of a merge operation performed on a golden record.
 */
final class MergeEvent extends BaseModel
{
    use HasUuids;

    protected $table = 'merge_event';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'id' => 'string',
            'occurred_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function goldenRecord(): BelongsTo
    {
        return $this->belongsTo(GoldenRecord::class, 'golden_record_id');
    }

    public function mergeRecords(): HasMany
    {
        return $this->hasMany(MergeRecord::class, 'merge_event_id');
    }

    public function mergeApprovals(): HasMany
    {
        return $this->hasMany(MergeApproval::class, 'merge_event_id');
    }

    public function survivorshipDecisions(): HasMany
    {
        return $this->hasMany(SurvivorshipDecision::class, 'merge_event_id');
    }
}
