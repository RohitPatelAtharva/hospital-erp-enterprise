<?php

declare(strict_types=1);

namespace App\Models\MasterData;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Categorizes contact records (e.g. phone, email).
 */
final class ContactType extends BaseModel
{
    use HasUuids;
    use SoftDeletes;

    protected $table = 'contact_type';

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

    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class, 'contact_type_id');
    }
}
