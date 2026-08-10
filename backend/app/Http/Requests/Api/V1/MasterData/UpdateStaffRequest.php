<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\MasterData;

use App\Http\Requests\BaseFormRequest;

/** Update staff request (10-API §8). Staff:update permission. */
final class UpdateStaffRequest extends BaseFormRequest
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
            'name' => ['sometimes', 'string', 'max:120'],
            'external_ref' => ['sometimes', 'nullable', 'string', 'max:100'],
        ];
    }
}
