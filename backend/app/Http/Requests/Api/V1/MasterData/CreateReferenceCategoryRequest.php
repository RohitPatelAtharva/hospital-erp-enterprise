<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\MasterData;

use App\Http\Requests\BaseFormRequest;

/** Create reference category request (10-API §11). Reference:manage permission. */
final class CreateReferenceCategoryRequest extends BaseFormRequest
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
            'code' => ['required', 'string', 'max:60'],
        ];
    }
}
