<?php

declare(strict_types=1);

namespace App\Models\MasterData;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Append-only record referencing an audited event. No soft deletes, no version.
 */
final class AuditReference extends BaseModel
{
    use HasUuids;

    protected $table = 'audit_reference';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'id' => 'string',
            'entity_id' => 'string',
            'occurred_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function action(): BelongsTo
    {
        return $this->belongsTo(AuditAction::class, 'audit_action_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(AuditActor::class, 'audit_actor_id');
    }
}
