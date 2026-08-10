<?php

declare(strict_types=1);

namespace App\Models\MasterData;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Defines source priority ordering for a survivorship attribute.
 */
final class AttributePriority extends BaseModel
{
    use HasUuids;
    use SoftDeletes;

    protected $table = 'attribute_priority';

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

    public function survivorshipRules(): HasMany
    {
        return $this->hasMany(SurvivorshipRule::class, 'attribute_priority_id');
    }
}
