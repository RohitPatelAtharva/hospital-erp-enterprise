<?php

declare(strict_types=1);

namespace App\Models\MasterData;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Links source master records to their golden record.
 */
final class GoldenRecordLink extends BaseModel
{
    use HasUuids;
    use SoftDeletes;

    protected $table = 'golden_record_link';

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

    public function goldenRecord(): BelongsTo
    {
        return $this->belongsTo(GoldenRecord::class, 'golden_record_id');
    }

    public function masterRecord(): BelongsTo
    {
        return $this->belongsTo(MasterRecord::class, 'master_record_id');
    }

    public function goldenRecordSources(): HasMany
    {
        return $this->hasMany(GoldenRecordSource::class, 'golden_record_link_id');
    }
}
