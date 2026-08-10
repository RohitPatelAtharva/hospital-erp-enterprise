<?php

declare(strict_types=1);

namespace App\Models\MasterData;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A candidate record flagged as a possible duplicate of a master record.
 */
final class DuplicateCandidate extends BaseModel
{
    use HasUuids;
    use SoftDeletes;

    protected $table = 'duplicate_candidate';

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

    public function candidateRecord(): BelongsTo
    {
        return $this->belongsTo(MasterRecord::class, 'candidate_record_id');
    }

    public function masterRecord(): BelongsTo
    {
        return $this->belongsTo(MasterRecord::class, 'master_record_id');
    }

    public function matchScores(): HasMany
    {
        return $this->hasMany(MatchScore::class, 'duplicate_candidate_id');
    }

    public function duplicateReviews(): HasMany
    {
        return $this->hasMany(DuplicateReview::class, 'duplicate_candidate_id');
    }
}
