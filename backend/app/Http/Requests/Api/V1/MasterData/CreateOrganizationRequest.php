<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\MasterData;

use App\Http\Requests\BaseFormRequest;

/** Create organization request (10-API §10). Organizations:create permission. */
final class CreateOrganizationRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('organizations:create') ?? false;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'organization_type_code' => ['nullable', 'string', 'max:40'],
            'external_ref' => ['nullable', 'string', 'max:100'],
        ];
    }
}
