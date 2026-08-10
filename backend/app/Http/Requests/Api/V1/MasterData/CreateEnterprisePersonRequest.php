<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\MasterData;

use App\Http\Requests\BaseFormRequest;

/** Create enterprise person request (10-API §12, EPI). Masterdata:read view + merge:execute on link. */
final class CreateEnterprisePersonRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('merge:execute') ?? false;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'dob' => ['nullable', 'date'],
        ];
    }
}
