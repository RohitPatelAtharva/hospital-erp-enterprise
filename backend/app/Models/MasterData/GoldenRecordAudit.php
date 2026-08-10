<?php

declare(strict_types=1);

namespace App\Models\MasterData;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Append-only audit trail of golden record changes.
 */
final class GoldenRecordAudit extends BaseModel
{
    use HasUuids;

    protected $table = 'golden_record_audit';

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

    public function goldenRecord(): BelongsTo
    {
        return $this->belongsTo(GoldenRecord::class, 'golden_record_id');
    }
}
