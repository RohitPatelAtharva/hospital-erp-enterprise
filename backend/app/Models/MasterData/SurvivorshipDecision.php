<?php

declare(strict_types=1);

namespace App\Models\MasterData;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Append-only record of a survivorship outcome for a merge event.
 */
final class SurvivorshipDecision extends BaseModel
{
    use HasUuids;

    protected $table = 'survivorship_decision';

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

    public function mergeEvent(): BelongsTo
    {
        return $this->belongsTo(MergeEvent::class, 'merge_event_id');
    }

    public function survivorshipRule(): BelongsTo
    {
        return $this->belongsTo(SurvivorshipRule::class, 'survivorship_rule_id');
    }
}
