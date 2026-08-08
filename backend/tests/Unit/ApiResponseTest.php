<?php

namespace Tests\Unit;

use App\Http\Responses\ApiResponse;
use Tests\TestCase;

class ApiResponseTest extends TestCase
{
    public function test_error_envelope_shape(): void
    {
        $response = ApiResponse::error('VALIDATION_ERROR', 'The given data was invalid.', ['field' => ['invalid']], 422);
        $payload = $response->getData(true);

        $this->assertSame(422, $response->getStatusCode());
        $this->assertSame('VALIDATION_ERROR', $payload['error']['code']);
        $this->assertSame([['field' => 'field', 'reason' => 'invalid']], $payload['error']['details']);
    }

    public function test_error_details_use_documented_shape(): void
    {
        // Laravel-style map is expanded into [{field, reason}] objects.
        $response = ApiResponse::error('VALIDATION_ERROR', 'Invalid.', [
            'email' => ['required', 'The email is invalid.'],
            'age' => ['min'],
        ]);
        $details = $response->getData(true)['error']['details'];

        $this->assertSame([
            ['field' => 'email', 'reason' => 'required'],
            ['field' => 'email', 'reason' => 'The email is invalid.'],
            ['field' => 'age', 'reason' => 'min'],
        ], $details);
    }

    public function test_error_details_are_passed_through_when_already_documented(): void
    {
        $response = ApiResponse::error('FORBIDDEN', 'No.', [['field' => 'orderId', 'reason' => 'not found']], 403);
        $details = $response->getData(true)['error']['details'];

        $this->assertSame([['field' => 'orderId', 'reason' => 'not found']], $details);
    }

    public function test_error_envelope_never_leaks_stack_traces(): void
    {
        $response = ApiResponse::error('SERVER_ERROR', 'An unexpected error occurred.', status: 500);
        $body = (string) $response->getContent();

        $this->assertStringNotContainsString('Exception', $body);
        $this->assertStringNotContainsString('Trace', $body);
        $this->assertStringNotContainsString('stack', $body);
    }

    public function test_data_envelope_shape(): void
    {
        $response = ApiResponse::data(['id' => 1], ['page' => 1], 200);
        $payload = $response->getData(true);

        $this->assertArrayHasKey('data', $payload);
        $this->assertSame(['page' => 1], $payload['meta']);
    }
}
