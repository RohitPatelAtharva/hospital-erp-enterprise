<?php

declare(strict_types=1);

namespace App\Models\MasterData;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A type of professional credential held by staff or providers.
 *
 * @property string $code
 */
final class CredentialType extends BaseModel
{
    use HasUuids;
    use SoftDeletes;

    protected $table = 'credential_type';

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

    public function staffCredentials(): HasMany
    {
        return $this->hasMany(StaffCredential::class, 'credential_type_id');
    }

    public function providerCredentials(): HasMany
    {
        return $this->hasMany(ProviderCredential::class, 'credential_type_id');
    }
}
