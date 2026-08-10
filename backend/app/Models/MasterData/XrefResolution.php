<?php

declare(strict_types=1);

namespace App\Models\MasterData;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Append-only record tracking xref resolution. No soft deletes, no version.
 */
final class XrefResolution extends BaseModel
{
    use HasUuids;

    protected $table = 'xref_resolution';

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

    public function crossReference(): BelongsTo
    {
        return $this->belongsTo(CrossReference::class, 'cross_reference_id');
    }
}
