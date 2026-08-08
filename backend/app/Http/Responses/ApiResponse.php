<?php

declare(strict_types=1);

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Standard API response envelope.
 *
 * Follows docs/11-API-STANDARDS.md §6:
 *   success -> { "data": ..., "meta": { "page", "pageSize", "total" } }
 *   error   -> { "error": { "code", "message", "details", "correlationId" } }
 *
 * Errors never leak stack traces or sensitive data.
 */
final class ApiResponse
{
    public static function data(mixed $data, array $meta = [], int $status = 200): JsonResponse
    {
        return response()->json(['data' => $data, 'meta' => $meta], $status);
    }

    public static function paginated(LengthAwarePaginator $paginator): JsonResponse
    {
        return self::data(
            $paginator->items(),
            [
                'page' => $paginator->currentPage(),
                'pageSize' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        );
    }

    public static function error(
        string $code,
        string $message,
        array $details = [],
        int $status = 422,
        ?string $correlationId = null,
    ): JsonResponse {
        $details = self::normalizeDetails($details);

        return response()->json([
            'error' => array_filter([
                'code' => $code,
                'message' => $message,
                'details' => $details === [] ? null : $details,
                'correlationId' => $correlationId ?? self::currentCorrelationId(),
            ], static fn ($value) => $value !== null),
        ], $status);
    }

    public static function created(mixed $data, array $meta = []): JsonResponse
    {
        return self::data($data, $meta, 201);
    }

    public static function noContent(): JsonResponse
    {
        return response()->json(null, 204);
    }

    private static function currentCorrelationId(): ?string
    {
        return app()->bound('request-correlation-id')
            ? (string) app('request-correlation-id')
            : null;
    }

    /**
     * Normalize error details to the documented shape
     * `[ { "field", "reason" }, ... ]` (docs/11-API-STANDARDS.md §6).
     *
     * Accepts a Laravel-style validation map `{ field: [reason, ...] }` and
     * expands it into one detail object per (field, reason) pair. Already
     * documented-shaped arrays are passed through unchanged.
     */
    private static function normalizeDetails(array $details): array
    {
        if ($details === []) {
            return [];
        }

        if (array_is_list($details)
            && isset($details[0])
            && is_array($details[0])
            && isset($details[0]['field'], $details[0]['reason'])) {
            return $details;
        }

        $normalized = [];
        foreach ($details as $field => $reasons) {
            foreach ((array) $reasons as $reason) {
                $normalized[] = [
                    'field' => (string) $field,
                    'reason' => (string) $reason,
                ];
            }
        }

        return $normalized;
    }
}
