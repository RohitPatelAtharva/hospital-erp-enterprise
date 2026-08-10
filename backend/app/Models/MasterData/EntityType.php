<?php

declare(strict_types=1);

namespace App\Models\MasterData;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A type of entity represented by a master record.
 *
 * @property string $code
 */
final class EntityType extends BaseModel
{
    use HasUuids;
    use SoftDeletes;

    protected $table = 'entity_type';

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

    public function masterRecords(): HasMany
    {
        return $this->hasMany(MasterRecord::class, 'entity_type_id');
    }
}
