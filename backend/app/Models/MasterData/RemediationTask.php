<?php

declare(strict_types=1);

namespace App\Models\MasterData;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A remediation task addressing a quality issue.
 */
final class RemediationTask extends BaseModel
{
    use HasUuids;
    use SoftDeletes;

    protected $table = 'remediation_task';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'id' => 'string',
            'version' => 'integer',
            'status' => 'string',
            'assignee_id' => 'string',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function qualityIssue(): BelongsTo
    {
        return $this->belongsTo(QualityIssue::class, 'quality_issue_id');
    }
}
