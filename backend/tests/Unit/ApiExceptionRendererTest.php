<?php

namespace Tests\Unit;

use App\Exceptions\ApiExceptionRenderer;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class ApiExceptionRendererTest extends TestCase
{
    public function test_renders_authorization_as_forbidden_envelope(): void
    {
        $response = app(ApiExceptionRenderer::class)->render(
            new AuthorizationException('Nope'),
            request(),
        );

        $this->assertSame(403, $response->getStatusCode());
        $this->assertSame('FORBIDDEN', $response->getData(true)['error']['code']);
    }

    public function test_renders_unauthenticated_as_401(): void
    {
        $response = app(ApiExceptionRenderer::class)->render(
            new AuthenticationException(),
            request(),
        );

        $this->assertSame(401, $response->getStatusCode());
        $this->assertSame('UNAUTHENTICATED', $response->getData(true)['error']['code']);
    }

    public function test_renders_unexpected_error_as_safe_500(): void
    {
        Log::spy();

        $response = app(ApiExceptionRenderer::class)->render(
            new \RuntimeException('secret database credentials leaked'),
            request(),
        );

        $this->assertSame(500, $response->getStatusCode());
        $this->assertSame('An unexpected error occurred.', $response->getData(true)['error']['message']);

        $body = (string) $response->getContent();
        $this->assertStringNotContainsString('secret database credentials', $body);
    }
}
