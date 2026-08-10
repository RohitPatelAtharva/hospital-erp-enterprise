<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\MasterData;

use App\Http\Requests\BaseFormRequest;

/** Create provider network membership (10-API §9). Providers:update permission. */
final class CreateProviderNetworkRequest extends BaseFormRequest
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
            'network_id' => ['required', 'uuid'],
        ];
    }
}
