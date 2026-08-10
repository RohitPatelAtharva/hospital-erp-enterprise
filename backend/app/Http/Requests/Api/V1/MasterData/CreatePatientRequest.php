<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\MasterData;

use App\Http\Requests\BaseFormRequest;

/**
 * Create patient request (10-API §7). Patients:create permission.
 */
final class CreatePatientRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('patients:create') ?? false;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'dob' => ['nullable', 'date'],
            'sex' => ['nullable', 'string', 'max:20'],
            'external_ref' => ['nullable', 'string', 'max:100'],
        ];
    }
}
