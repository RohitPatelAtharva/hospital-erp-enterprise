<?php

declare(strict_types=1);

namespace App\Models\MasterData;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final class GoldenRecord extends BaseModel
{
    use HasUuids;
    use SoftDeletes;

    protected $table = 'golden_record';

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

    public function masterRecord(): BelongsTo
    {
        return $this->belongsTo(MasterRecord::class, 'master_record_id');
    }

    public function goldenRecordLinks(): HasMany
    {
        return $this->hasMany(GoldenRecordLink::class, 'golden_record_id');
    }

    public function goldenRecordAudits(): HasMany
    {
        return $this->hasMany(GoldenRecordAudit::class, 'golden_record_id');
    }

    public function mergeEvents(): HasMany
    {
        return $this->hasMany(MergeEvent::class, 'golden_record_id');
    }
}
