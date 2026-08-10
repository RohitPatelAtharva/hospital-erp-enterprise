<?php

declare(strict_types=1);

namespace App\Models\MasterData;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Configurable matching rule definition.
 */
final class MatchRule extends BaseModel
{
    use HasUuids;
    use SoftDeletes;

    protected $table = 'match_rule';

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

    public function matchScores(): HasMany
    {
        return $this->hasMany(MatchScore::class, 'match_rule_id');
    }

    public function matchThresholds(): HasMany
    {
        return $this->hasMany(MatchThreshold::class, 'match_rule_id');
    }
}
