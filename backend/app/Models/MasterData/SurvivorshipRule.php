<?php

declare(strict_types=1);

namespace App\Models\MasterData;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Configurable survivorship rule that selects the winning attribute source.
 */
final class SurvivorshipRule extends BaseModel
{
    use HasUuids;
    use SoftDeletes;

    protected $table = 'survivorship_rule';

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

    public function attributePriority(): BelongsTo
    {
        return $this->belongsTo(AttributePriority::class, 'attribute_priority_id');
    }

    public function survivorshipDecisions(): HasMany
    {
        return $this->hasMany(SurvivorshipDecision::class, 'survivorship_rule_id');
    }
}
