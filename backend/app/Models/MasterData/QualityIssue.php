<?php

declare(strict_types=1);

namespace App\Models\MasterData;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A data quality issue raised against a master record.
 */
final class QualityIssue extends BaseModel
{
    use HasUuids;
    use SoftDeletes;

    protected $table = 'quality_issue';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'id' => 'string',
            'version' => 'integer',
            'status' => 'string',
            'reported_by' => 'string',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function masterRecord(): BelongsTo
    {
        return $this->belongsTo(MasterRecord::class, 'master_record_id');
    }

    public function remediationTasks(): HasMany
    {
        return $this->hasMany(RemediationTask::class, 'quality_issue_id');
    }

    public function stewardshipLogs(): HasMany
    {
        return $this->hasMany(StewardshipLog::class, 'quality_issue_id');
    }
}
