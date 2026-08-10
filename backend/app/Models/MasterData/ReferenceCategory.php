<?php

declare(strict_types=1);

namespace App\Models\MasterData;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A grouping category for reference values.
 *
 * @property string $code
 */
final class ReferenceCategory extends BaseModel
{
    use HasUuids;
    use SoftDeletes;

    protected $table = 'reference_category';

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

    public function values(): HasMany
    {
        return $this->hasMany(ReferenceValue::class, 'reference_category_id');
    }
}
