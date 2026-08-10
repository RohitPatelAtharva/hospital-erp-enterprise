<?php

declare(strict_types=1);

namespace App\Models\MasterData;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

final class CrossReference extends BaseModel
{
    use HasUuids;
    use SoftDeletes;

    protected $table = 'cross_reference';

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

    public function masterRecord(): BelongsTo
    {
        return $this->belongsTo(MasterRecord::class, 'master_record_id');
    }

    public function xrefType(): BelongsTo
    {
        return $this->belongsTo(XrefType::class, 'xref_type_id');
    }

    public function xrefResolution(): HasOne
    {
        return $this->hasOne(XrefResolution::class, 'cross_reference_id');
    }
}
