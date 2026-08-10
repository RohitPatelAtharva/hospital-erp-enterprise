<?php

declare(strict_types=1);

namespace App\Models\MasterData;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final class DepartmentReference extends BaseModel
{
    use HasUuids;
    use SoftDeletes;

    protected $table = 'department_reference';

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

    public function facilityReference(): BelongsTo
    {
        return $this->belongsTo(FacilityReference::class, 'facility_reference_id');
    }

    public function units(): HasMany
    {
        return $this->hasMany(UnitReference::class, 'department_reference_id');
    }
}
