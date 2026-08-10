<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\MasterData;

use App\Http\Requests\BaseFormRequest;

/** Create provider request (10-API §9). Providers:create permission. */
final class CreateProviderRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('providers:create') ?? false;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'type' => ['nullable', 'string', 'max:40'],
            'external_ref' => ['nullable', 'string', 'max:100'],
        ];
    }
}
