<?php

declare(strict_types=1);

namespace App\Models\MasterData;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Append-only record tracking identity assignment. No soft deletes, no version.
 */
final class IdentityAssignment extends BaseModel
{
    use HasUuids;

    protected $table = 'identity_assignment';

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

    public function identityRecord(): BelongsTo
    {
        return $this->belongsTo(IdentityRecord::class, 'identity_record_id');
    }
}
