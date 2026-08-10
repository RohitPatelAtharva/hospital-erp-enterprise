<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\MasterData;

use App\Http\Requests\BaseFormRequest;

/** Update provider request (10-API §9). Providers:update permission. */
final class UpdateProviderRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('providers:update') ?? false;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:120'],
            'type' => ['sometimes', 'nullable', 'string', 'max:50'],
            'external_ref' => ['sometimes', 'nullable', 'string', 'max:100'],
        ];
    }
}
