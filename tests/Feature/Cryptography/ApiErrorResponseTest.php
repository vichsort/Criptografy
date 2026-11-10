<?php

namespace Tests\Feature\Cryptography;

use Illuminate\Support\Facades\Exceptions;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Clientes reais (ex.: fetch() do navegador) não enviam
 * "Accept: application/json" por padrão. Os demais testes usam postJson(),
 * que envia esse header e por isso não detectariam respostas HTML ou
 * redirects. Aqui as requisições imitam um fetch() comum (Accept: *\/*).
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
        yield 'algoritmo inexistente' => ['/api/encrypt', ['algorithm' => 'nope', 'input' => 'a'], 404];
        yield 'operação não suportada' => ['/api/encrypt', ['algorithm' => 'sha-256', 'input' => 'a'], 422];
        yield 'opções inválidas' => ['/api/encrypt', ['algorithm' => 'caesar', 'input' => 'a'], 422];
        yield 'resultado não UTF-8' => ['/api/decode', ['algorithm' => 'hex', 'input' => 'ff'], 422];
    }

    /**
     * Erros causados pela entrada do cliente não são falhas da aplicação:
     * registrá-los no log (com stacktrace) só gera ruído em produção.
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
