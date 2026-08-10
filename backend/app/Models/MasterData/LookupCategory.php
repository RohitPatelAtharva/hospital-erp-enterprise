<?php

declare(strict_types=1);

namespace App\Models\MasterData;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final class LookupCategory extends BaseModel
{
    use HasUuids;
    use SoftDeletes;

    protected $table = 'lookup_category';

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

    public function lookups(): HasMany
    {
        return $this->hasMany(Lookup::class, 'lookup_category_id');
    }

    public function values(): HasMany
    {
        return $this->hasMany(LookupValue::class, 'lookup_category_id');
    }
}
