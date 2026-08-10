<?php

declare(strict_types=1);

namespace App\Models\MasterData;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A master data domain assigned to stewards.
 *
 * @property string $code
 */
final class MasterDomain extends BaseModel
{
    use HasUuids;
    use SoftDeletes;

    protected $table = 'master_domain';

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

    public function stewardAssignments(): HasMany
    {
        return $this->hasMany(StewardAssignment::class, 'master_domain_id');
    }
}
