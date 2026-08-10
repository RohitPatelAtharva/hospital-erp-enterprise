<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\MasterData;

use App\Http\Requests\BaseFormRequest;

/** Update patient request (10-API §7). Patients:update permission. */
final class UpdatePatientRequest extends BaseFormRequest
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
            'name' => ['sometimes', 'string', 'max:120'],
            'dob' => ['sometimes', 'nullable', 'date'],
            'sex' => ['sometimes', 'nullable', 'string', 'max:20'],
            'external_ref' => ['sometimes', 'nullable', 'string', 'max:100'],
        ];
    }
}
