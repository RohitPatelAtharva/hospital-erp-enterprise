<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\MasterData;

use App\Http\Requests\BaseFormRequest;

/** Create patient alias (10-API §7). Patients:update permission. */
final class CreatePatientAliasRequest extends BaseFormRequest
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
            'name' => ['required', 'string', 'max:255'],
        ];
    }
}
