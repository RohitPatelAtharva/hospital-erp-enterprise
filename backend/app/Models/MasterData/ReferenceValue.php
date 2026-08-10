<?php

declare(strict_types=1);

namespace App\Models\MasterData;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A concrete reference value belonging to a category and version.
 *
 * @property string $code
 */
final class ReferenceValue extends BaseModel
{
    use HasUuids;
    use SoftDeletes;

    protected $table = 'reference_value';

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

    public function category(): BelongsTo
    {
        return $this->belongsTo(ReferenceCategory::class, 'reference_category_id');
    }

    public function referenceVersion(): BelongsTo
    {
        return $this->belongsTo(ReferenceVersion::class, 'reference_version_id');
    }
}
