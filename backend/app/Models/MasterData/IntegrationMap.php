<?php

declare(strict_types=1);

namespace App\Models\MasterData;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final class IntegrationMap extends BaseModel
{
    use HasUuids;
    use SoftDeletes;

    protected $table = 'integration_map';

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

    public function endpoint(): BelongsTo
    {
        return $this->belongsTo(IntegrationEndpoint::class, 'integration_endpoint_id');
    }

    public function mappingFields(): HasMany
    {
        return $this->hasMany(MappingField::class, 'integration_map_id');
    }
}
