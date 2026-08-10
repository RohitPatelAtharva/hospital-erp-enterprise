<?php

declare(strict_types=1);

namespace App\Models\MasterData;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A provider (e.g. clinician or facility) record in the master data module.
 */
final class Provider extends BaseModel
{
    use HasUuids;
    use SoftDeletes;

    protected $table = 'provider';

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

    public function providerCredentials(): HasMany
    {
        return $this->hasMany(ProviderCredential::class, 'provider_id');
    }

    public function providerNetworks(): HasMany
    {
        return $this->hasMany(ProviderNetwork::class, 'provider_id');
    }

    public function providerIdentifiers(): HasMany
    {
        return $this->hasMany(ProviderIdentifier::class, 'provider_id');
    }
}
