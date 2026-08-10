<?php

declare(strict_types=1);

namespace App\Models\MasterData;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Append-only log of stewardship actions on a quality issue.
 */
final class StewardshipLog extends BaseModel
{
    use HasUuids;

    protected $table = 'stewardship_log';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'id' => 'string',
            'actor_id' => 'string',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function qualityIssue(): BelongsTo
    {
        return $this->belongsTo(QualityIssue::class, 'quality_issue_id');
    }
}
