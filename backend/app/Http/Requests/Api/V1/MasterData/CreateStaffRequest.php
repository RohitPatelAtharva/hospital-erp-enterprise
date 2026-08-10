<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\MasterData;

use App\Http\Requests\BaseFormRequest;

/** Create staff request (10-API §8). Staff:create permission. */
final class CreateStaffRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('staff:create') ?? false;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'dob' => ['nullable', 'date'],
            'external_ref' => ['nullable', 'string', 'max:100'],
        ];
    }
}
