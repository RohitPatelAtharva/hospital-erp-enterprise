<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\MasterData;

use App\Http\Requests\BaseFormRequest;

/** Create provider identifier (10-API §9). Providers:update permission. */
final class CreateProviderIdentifierRequest extends BaseFormRequest
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
            'identity_type_id' => ['required', 'uuid'],
            'value' => ['required', 'string', 'max:255'],
        ];
    }
}
