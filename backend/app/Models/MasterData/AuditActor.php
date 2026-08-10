<?php

declare(strict_types=1);

namespace App\Models\MasterData;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Append-only record identifying an audit actor. No soft deletes, no version.
 */
final class AuditActor extends BaseModel
{
    use HasUuids;

    protected $table = 'audit_actor';

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

    public function auditReferences(): HasMany
    {
        return $this->hasMany(AuditReference::class, 'audit_actor_id');
    }
}
