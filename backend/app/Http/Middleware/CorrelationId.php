<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Attaches a correlation (request) id to every request.
 *
 * The id is reused from an inbound `X-Correlation-Id` header when present,
 * otherwise generated, and is bound to the container + log context so services,
 * audit records, and errors all share one trace id. It is returned on the
 * response for client correlation.
 */
final class CorrelationId
{
    public function handle(Request $request, Closure $next): Response
    {
        $correlationId = $request->header('X-Correlation-Id', (string) Str::uuid());

        app()->instance('request-correlation-id', $correlationId);
        Log::withContext(['correlation_id' => $correlationId]);

        $response = $next($request);
        $response->headers->set('X-Correlation-Id', $correlationId);

        return $response;
    }
}
