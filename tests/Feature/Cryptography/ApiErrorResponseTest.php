<?php

namespace Tests\Feature\Cryptography;

use Illuminate\Support\Facades\Exceptions;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

/**
 * Real clients (e.g. the browser's fetch()) do not send
 * "Accept: application/json" by default. The other tests use postJson(),
 * which sends that header and therefore would not catch HTML responses or
 * redirects. Here requests mimic a plain fetch() (Accept: *\/*).
 */
final class ApiErrorResponseTest extends TestCase
{
    public function test_validation_error_returns_json_422_without_accept_header(): void
    {
        $response = $this->fetchLike('POST', '/api/encrypt', ['algorithm' => 'caesar']);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('input');
    }

    public function test_unknown_api_route_returns_json_404_without_accept_header(): void
    {
        $response = $this->fetchLike('GET', '/api/does-not-exist');

        $response->assertNotFound();
        $response->assertJsonStructure(['message']);
    }

    public function test_wrong_http_method_returns_json_405_without_accept_header(): void
    {
        $response = $this->fetchLike('GET', '/api/encrypt');

        $response->assertStatus(405);
        $response->assertJsonStructure(['message']);
    }

    /**
     * @return iterable<string, array{string, array<string, mixed>, int}>
     */
    public static function clientErrors(): iterable
    {
        yield 'unknown algorithm' => ['/api/encrypt', ['algorithm' => 'nope', 'input' => 'a'], 404];
        yield 'unsupported operation' => ['/api/encrypt', ['algorithm' => 'sha-256', 'input' => 'a'], 422];
        yield 'invalid options' => ['/api/encrypt', ['algorithm' => 'caesar', 'input' => 'a'], 422];
        yield 'non-UTF-8 result' => ['/api/decode', ['algorithm' => 'hex', 'input' => 'ff'], 422];
    }

    /**
     * Errors caused by client input are not application failures: logging
     * them (with a stack trace) only adds noise in production.
     *
     * @param  array<string, mixed>  $body
     */
    #[DataProvider('clientErrors')]
    public function test_client_errors_are_not_reported_to_the_log(string $uri, array $body, int $status): void
    {
        Exceptions::fake();

        $this->postJson($uri, $body)->assertStatus($status);

        Exceptions::assertNothingReported();
    }

    /**
     * @param  array<string, mixed>|null  $body
     * @return TestResponse<Response>
     */
    private function fetchLike(string $method, string $uri, ?array $body = null): TestResponse
    {
        $server = ['HTTP_ACCEPT' => '*/*'];

        if ($body !== null) {
            $server['CONTENT_TYPE'] = 'application/json';
        }

        return $this->call(
            $method,
            $uri,
            server: $server,
            content: $body === null ? null : json_encode($body, JSON_THROW_ON_ERROR),
        );
    }
}
