<?php

declare(strict_types=1);

namespace App\Models\MasterData;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A reusable, type-addressable address record.
 */
final class Address extends BaseModel
{
    use HasUuids;
    use SoftDeletes;

    protected $table = 'address';

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

    public function addressType(): BelongsTo
    {
        return $this->belongsTo(AddressType::class, 'address_type_id');
    }

    public function postalCode(): BelongsTo
    {
        return $this->belongsTo(PostalCode::class, 'postal_code_id');
    }

    public function addressValidation(): HasOne
    {
        return $this->hasOne(AddressValidation::class, 'address_id');
    }
}
