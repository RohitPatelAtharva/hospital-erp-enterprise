<?php

declare(strict_types=1);

namespace App\Models\MasterData;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final class IdentityRecord extends BaseModel
{
    use HasUuids;
    use SoftDeletes;

    protected $table = 'identity_record';

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

    public function identityType(): BelongsTo
    {
        return $this->belongsTo(IdentityType::class, 'identity_type_id');
    }

    public function identityIssuer(): BelongsTo
    {
        return $this->belongsTo(IdentityIssuer::class, 'identity_issuer_id');
    }

    public function identityAssignments(): HasMany
    {
        return $this->hasMany(IdentityAssignment::class, 'identity_record_id');
    }
}
