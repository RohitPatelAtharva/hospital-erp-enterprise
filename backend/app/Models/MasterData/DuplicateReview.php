<?php

declare(strict_types=1);

namespace App\Models\MasterData;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Append-only steward review of a duplicate candidate.
 */
final class DuplicateReview extends BaseModel
{
    use HasUuids;

    protected $table = 'duplicate_review';

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

    public function duplicateCandidate(): BelongsTo
    {
        return $this->belongsTo(DuplicateCandidate::class, 'duplicate_candidate_id');
    }
}
