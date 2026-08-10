<?php

declare(strict_types=1);

namespace App\Models\MasterData;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final class EnterprisePerson extends BaseModel
{
    use HasUuids;
    use SoftDeletes;

    protected $table = 'enterprise_person';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'id' => 'string',
            'version' => 'integer',
            'status' => 'string',
            'dob' => 'date',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function patients(): HasMany
    {
        return $this->hasMany(Patient::class, 'enterprise_person_id');
    }

    public function staff(): HasMany
    {
        return $this->hasMany(Staff::class, 'enterprise_person_id');
    }
}
