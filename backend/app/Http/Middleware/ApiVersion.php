<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Http\Responses\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * API version handling (docs/11-API-STANDARDS.md §5).
 *
 * The requested version is read from the `X-Api-Version` header (defaulting to
 * `v1`) and validated against the supported set. Unsupported versions are
 * rejected with a stable error code; the resolved version is stored on the
 * request attributes for later dispatch.
 */
final class ApiVersion
{
    public const DEFAULT = 'v1';

    public const SUPPORTED = ['v1'];

    public function handle(Request $request, Closure $next): Response
    {
        $version = $request->header('X-Api-Version', self::DEFAULT);

        if (! in_array($version, self::SUPPORTED, true)) {
            return ApiResponse::error(
                code: 'UNSUPPORTED_API_VERSION',
                message: "API version [{$version}] is not supported.",
                status: 400,
            );
        }

        $request->attributes->set('api_version', $version);

        return $next($request);
    }
}
