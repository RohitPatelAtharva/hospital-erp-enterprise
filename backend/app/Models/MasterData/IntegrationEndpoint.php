<?php

declare(strict_types=1);

namespace App\Models\MasterData;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Represents a reusable integration endpoint for export/mapping.
 */
final class IntegrationEndpoint extends BaseModel
{
    use HasUuids;
    use SoftDeletes;

    protected $table = 'integration_endpoint';

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

    public function exportRecipients(): HasMany
    {
        return $this->hasMany(ExportRecipient::class, 'integration_endpoint_id');
    }

    public function integrationMaps(): HasMany
    {
        return $this->hasMany(IntegrationMap::class, 'integration_endpoint_id');
    }
}
