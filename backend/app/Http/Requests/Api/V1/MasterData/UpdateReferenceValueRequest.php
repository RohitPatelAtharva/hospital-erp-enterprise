<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\MasterData;

use App\Http\Requests\BaseFormRequest;

/** Update reference value request (10-API §11). Reference:manage permission. */
final class UpdateReferenceValueRequest extends BaseFormRequest
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
            'label' => ['sometimes', 'string', 'max:120'],
            'value' => ['sometimes', 'string', 'max:120'],
        ];
    }
}
