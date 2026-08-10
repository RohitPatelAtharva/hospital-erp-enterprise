<?php

declare(strict_types=1);

namespace App\Models\MasterData;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Storage location for documents. No soft deletes, no version.
 */
final class DocumentStorage extends BaseModel
{
    use HasUuids;

    protected $table = 'document_storage';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'id' => 'string',
            'status' => 'string',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function documents(): HasMany
    {
        return $this->hasMany(MasterDocument::class, 'document_storage_id');
    }
}
