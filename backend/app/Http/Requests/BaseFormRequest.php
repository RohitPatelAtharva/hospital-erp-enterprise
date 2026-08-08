<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Http\Responses\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * Base form request.
 *
 * Standardizes validation and authorization failures into the API error
 * envelope (docs/11-API-STANDARDS.md §6) so API responses stay consistent.
 * Subclasses must implement authorize() (permission check) and rules().
 */
abstract class BaseFormRequest extends FormRequest
{
    abstract public function authorize(): bool;

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            ApiResponse::error(
                code: 'VALIDATION_ERROR',
                message: 'The given data was invalid.',
                details: $validator->errors()->toArray(),
                status: 422,
            ),
        );
    }

    protected function failedAuthorization(): void
    {
        throw new HttpResponseException(
            ApiResponse::error(
                code: 'FORBIDDEN',
                message: 'You are not authorized to perform this action.',
                status: 403,
            ),
        );
    }
}
