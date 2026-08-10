<?php

declare(strict_types=1);

namespace App\Models\MasterData;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

final class MasterRecord extends BaseModel
{
    use HasUuids;
    use SoftDeletes;

    protected $table = 'master_record';

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

    public function entityType(): BelongsTo
    {
        return $this->belongsTo(EntityType::class, 'entity_type_id');
    }

    public function goldenRecord(): HasOne
    {
        return $this->hasOne(GoldenRecord::class, 'master_record_id');
    }

    public function crossReferences(): HasMany
    {
        return $this->hasMany(CrossReference::class, 'master_record_id');
    }

    public function identityRecords(): HasMany
    {
        return $this->hasMany(IdentityRecord::class, 'master_record_id');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(Version::class, 'master_record_id');
    }

    public function qualityIssues(): HasMany
    {
        return $this->hasMany(QualityIssue::class, 'master_record_id');
    }

    public function mergeRecords(): HasMany
    {
        return $this->hasMany(MergeRecord::class, 'master_record_id');
    }

    public function patients(): HasMany
    {
        return $this->hasMany(Patient::class, 'master_record_id');
    }

    public function staff(): HasMany
    {
        return $this->hasMany(Staff::class, 'master_record_id');
    }

    public function providers(): HasMany
    {
        return $this->hasMany(Provider::class, 'master_record_id');
    }

    public function organizations(): HasMany
    {
        return $this->hasMany(Organization::class, 'master_record_id');
    }
}
