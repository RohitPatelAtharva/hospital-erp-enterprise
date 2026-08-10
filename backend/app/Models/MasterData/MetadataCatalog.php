<?php

declare(strict_types=1);

namespace App\Models\MasterData;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final class MetadataCatalog extends BaseModel
{
    use HasUuids;
    use SoftDeletes;

    protected $table = 'metadata_catalog';

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

    public function schemaMetadata(): HasMany
    {
        return $this->hasMany(SchemaMetadata::class, 'metadata_catalog_id');
    }

    public function dataDictionaries(): HasMany
    {
        return $this->hasMany(DataDictionary::class, 'metadata_catalog_id');
    }
}
