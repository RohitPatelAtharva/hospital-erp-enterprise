<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Http\Responses\ApiResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

/**
 * Converts exceptions into the standard API error envelope.
 *
 * Never leaks stack traces or sensitive data (docs/11-API-STANDARDS.md §6,
 * 04-CODING-STANDARDS §7). Unexpected errors are reported for operators and a
 * generic, safe message is returned to the client.
 */
final class ApiExceptionRenderer
{
    public function render(Throwable $exception, mixed $request): JsonResponse
    {
        if ($exception instanceof ValidationException) {
            return ApiResponse::error(
                'VALIDATION_ERROR',
                'The given data was invalid.',
                $exception->errors(),
                422,
            );
        }

        if ($exception instanceof AuthenticationException) {
            return ApiResponse::error('UNAUTHENTICATED', 'Unauthenticated.', status: 401);
        }

        if ($exception instanceof AuthorizationException) {
            return ApiResponse::error(
                'FORBIDDEN',
                $exception->getMessage() !== '' ? $exception->getMessage() : 'Forbidden.',
                status: 403,
            );
        }

        if ($exception instanceof ModelNotFoundException) {
            return ApiResponse::error('NOT_FOUND', 'Resource not found.', status: 404);
        }

        if ($exception instanceof HttpExceptionInterface) {
            // Gate::authorize / the `can:` middleware raise AccessDeniedHttpException
            // (403) and UnauthorizedHttpException (401), which are not
            // AuthorizationException/AuthenticationException. Likewise Laravel's
            // exception handler converts ModelNotFoundException into a 404
            // NotFoundHttpException before this renderer runs. Map their status
            // codes to the documented semantic codes (docs/11-API-STANDARDS.md §6).
            $code = match ($exception->getStatusCode()) {
                401 => 'UNAUTHENTICATED',
                403 => 'FORBIDDEN',
                404 => 'NOT_FOUND',
                default => 'HTTP_ERROR',
            };

            return ApiResponse::error(
                $code,
                $exception->getMessage() !== '' ? $exception->getMessage() : 'Request failed.',
                status: $exception->getStatusCode(),
            );
        }

        report($exception);

        return ApiResponse::error('SERVER_ERROR', 'An unexpected error occurred.', status: 500);
    }
}
