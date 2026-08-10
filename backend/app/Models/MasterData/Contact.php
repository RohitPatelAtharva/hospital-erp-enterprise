<?php

declare(strict_types=1);

namespace App\Models\MasterData;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A typed, reusable contact value (phone, email, etc.).
 */
final class Contact extends BaseModel
{
    use HasUuids;
    use SoftDeletes;

    protected $table = 'contact';

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

    public function contactType(): BelongsTo
    {
        return $this->belongsTo(ContactType::class, 'contact_type_id');
    }

    public function contactUse(): BelongsTo
    {
        return $this->belongsTo(ContactUse::class, 'contact_use_id');
    }

    public function organizationContacts(): HasMany
    {
        return $this->hasMany(OrganizationContact::class, 'contact_id');
    }

    public function contactPreferences(): HasMany
    {
        return $this->hasMany(ContactPreference::class, 'contact_id');
    }
}
