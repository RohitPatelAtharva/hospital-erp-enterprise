<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\MasterData;

use App\Http\Requests\BaseFormRequest;

/**
 * Rotate an entity identifier (10-API §7-§10).
 *
 * The write permission is validated by the route middleware for the owning
 * entity; this request only enforces payload shape.
 */
final class RotateIdentifierRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'value' => ['sometimes', 'nullable', 'string', 'max:255'],
        ];
    }
}
