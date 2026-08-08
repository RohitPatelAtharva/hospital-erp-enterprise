<?php

use App\Exceptions\ApiExceptionRenderer;
use App\Http\Middleware\ApiVersion;
use App\Http\Middleware\CorrelationId;
use App\Http\Middleware\RequireTenantScope;
use App\Http\Middleware\SetTenantContext;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        apiPrefix: 'api',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Global: attach a correlation id before anything logs.
        $middleware->append(CorrelationId::class);

        // API group: stateful Sanctum support; throttle applied via routes.
        $middleware->group('api', [
            EnsureFrontendRequestsAreStateful::class,
        ]);

        // Route aliases.
        $middleware->alias([
            'tenant' => SetTenantContext::class,
            'tenant.required' => RequireTenantScope::class,
            'api.version' => ApiVersion::class,
            'abilities' => \Laravel\Sanctum\Http\Middleware\CheckAbilities::class,
            'ability' => \Laravel\Sanctum\Http\Middleware\CheckForAnyAbility::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (Throwable $e, $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return app(ApiExceptionRenderer::class)->render($e, $request);
            }
        });
    })->create();
