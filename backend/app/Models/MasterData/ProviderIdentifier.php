<?php

declare(strict_types=1);

namespace App\Models\MasterData;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * An identifier value assigned to a provider.
 */
final class ProviderIdentifier extends BaseModel
{
    use HasUuids;
    use SoftDeletes;

    protected $table = 'provider_identifier';

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

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class, 'provider_id');
    }

    public function identityType(): BelongsTo
    {
        return $this->belongsTo(IdentityType::class, 'identity_type_id');
    }
}
