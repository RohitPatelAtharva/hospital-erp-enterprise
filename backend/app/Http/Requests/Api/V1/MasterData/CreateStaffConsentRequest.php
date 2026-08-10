<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\MasterData;

use App\Http\Requests\BaseFormRequest;

/** Create staff consent (10-API §8). Staff:update permission. */
final class CreateStaffConsentRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('staff:update') ?? false;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'consent_type_id' => ['required', 'uuid'],
        ];
    }
}
