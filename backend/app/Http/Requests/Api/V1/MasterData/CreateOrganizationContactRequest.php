<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\MasterData;

use App\Http\Requests\BaseFormRequest;

/** Create organization contact (10-API §10). Organizations:update permission. */
final class CreateOrganizationContactRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('organizations:update') ?? false;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'contact_id' => ['required', 'uuid'],
        ];
    }
}
