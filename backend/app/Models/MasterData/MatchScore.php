<?php

declare(strict_types=1);

namespace App\Models\MasterData;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Append-only confidence score for a duplicate candidate against a match rule.
 */
final class MatchScore extends BaseModel
{
    use HasUuids;

    protected $table = 'match_score';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'id' => 'string',
            'value' => 'float',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function duplicateCandidate(): BelongsTo
    {
        return $this->belongsTo(DuplicateCandidate::class, 'duplicate_candidate_id');
    }

    public function matchRule(): BelongsTo
    {
        return $this->belongsTo(MatchRule::class, 'match_rule_id');
    }
}
