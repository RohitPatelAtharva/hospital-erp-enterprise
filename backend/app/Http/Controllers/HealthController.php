<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Health-check foundation (docs/modules/master-data/21-Deployment.md).
 *
 * Reports application, database, and cache liveness without exposing sensitive
 * detail. Protected resources are covered by the tenant/authz chain; health is
 * intentionally open for orchestration probes.
 */
final class HealthController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $status = 'ok';
        $checks = [
            'app' => 'ok',
            'database' => $this->databaseOk(),
            'cache' => $this->cacheOk(),
        ];

        if (in_array('error', $checks, true)) {
            $status = 'degraded';
        }

        return ApiResponse::data(
            ['status' => $status, 'checks' => $checks],
            ['service' => config('app.name')],
            $status === 'ok' ? 200 : 503,
        );
    }

    private function databaseOk(): string
    {
        try {
            DB::connection()->getPdo();

            return 'ok';
        } catch (\Throwable) {
            return 'error';
        }
    }

    private function cacheOk(): string
    {
        try {
            Cache::put('health.check', true, now()->addSecond());

            return Cache::get('health.check') === true ? 'ok' : 'error';
        } catch (\Throwable) {
            return 'error';
        }
    }
}
