<?php

namespace Tests\Feature\Infrastructure;

use Illuminate\Testing\TestResponse;
use Tests\TestCase;

final class RateLimitTest extends TestCase
{
    public function test_exceeding_the_rate_limit_returns_json_429(): void
    {
        config(['app.api_rate_limit' => 2]);

        $this->hash()->assertOk();
        $this->hash()->assertOk();

        $response = $this->hash();

        $response->assertStatus(429);
        $response->assertJsonStructure(['message']);
    }

    public function test_clients_behind_a_proxy_are_limited_by_their_own_ip(): void
    {
        // No Render todas as requisições chegam pelo proxy da plataforma. Sem
        // confiar no X-Forwarded-For, todos os clientes dividiriam um único
        // limite (o do IP do proxy).
        config(['app.api_rate_limit' => 1]);

        $this->hash(forwardedFor: '203.0.113.1')->assertOk();
        $this->hash(forwardedFor: '203.0.113.2')->assertOk();
        $this->hash(forwardedFor: '203.0.113.1')->assertStatus(429);
    }

    private function hash(?string $forwardedFor = null): TestResponse
    {
        $headers = $forwardedFor === null ? [] : ['X-Forwarded-For' => $forwardedFor];

        return $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.1'])
            ->postJson('/api/hash', ['algorithm' => 'sha-256', 'input' => 'abc'], $headers);
    }
}
