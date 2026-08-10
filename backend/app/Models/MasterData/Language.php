<?php

declare(strict_types=1);

namespace App\Models\MasterData;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Language extends BaseModel
{
    use HasUuids;
    use SoftDeletes;

    protected $table = 'language';

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

    public function preferences(): HasMany
    {
        return $this->hasMany(LanguagePreference::class, 'language_id');
    }

    public function proficiencies(): HasMany
    {
        return $this->hasMany(LanguageProficiency::class, 'language_id');
    }
}
