<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\MasterData;

use App\Http\Requests\BaseFormRequest;

/** Create reference value request (10-API §11). Reference:manage permission. */
final class CreateReferenceValueRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('reference:manage') ?? false;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:40'],
            'reference_category_id' => ['nullable', 'uuid'],
            'category_code' => ['required_without:reference_category_id', 'string', 'max:60'],
            'reference_version_id' => ['nullable', 'uuid'],
            'version_code' => ['nullable', 'string', 'max:40'],
        ];
    }
}
