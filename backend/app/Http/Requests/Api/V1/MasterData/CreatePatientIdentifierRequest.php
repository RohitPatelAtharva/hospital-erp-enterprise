<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\MasterData;

use App\Http\Requests\BaseFormRequest;

/** Create patient identifier (10-API §7). Patients:update permission. */
final class CreatePatientIdentifierRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('patients:update') ?? false;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'identity_type_id' => ['required', 'uuid'],
            'value' => ['required', 'string', 'max:255'],
        ];
    }
}
