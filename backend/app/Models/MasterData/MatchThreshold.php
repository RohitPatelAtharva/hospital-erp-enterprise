<?php

declare(strict_types=1);

namespace App\Models\MasterData;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Confidence thresholds configured per match rule.
 */
final class MatchThreshold extends BaseModel
{
    use HasUuids;
    use SoftDeletes;

    protected $table = 'match_threshold';

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

    public function matchRule(): BelongsTo
    {
        return $this->belongsTo(MatchRule::class, 'match_rule_id');
    }
}
