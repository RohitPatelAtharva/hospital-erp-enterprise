<?php

declare(strict_types=1);

namespace App\Models\MasterData;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Append-only version record of a master record.
 */
final class Version extends BaseModel
{
    use HasUuids;

    protected $table = 'version';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'id' => 'string',
            'version_number' => 'integer',
            'actor_id' => 'string',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function masterRecord(): BelongsTo
    {
        return $this->belongsTo(MasterRecord::class, 'master_record_id');
    }

    public function snapshot(): HasOne
    {
        return $this->hasOne(VersionSnapshot::class, 'version_id');
    }

    public function audit(): HasOne
    {
        return $this->hasOne(VersionAudit::class, 'version_id');
    }
}
