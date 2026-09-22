<?php

namespace Tests\Feature\Cryptography;

use Tests\TestCase;

final class ApiStatusTest extends TestCase
{
    public function test_root_returns_healthy_api_status(): void
    {
        $response = $this->getJson('/');

        $response->assertOk();
        $response->assertJson([
            'name' => 'Criptografy API',
            'status' => 'healthy',
            'version' => '1.0.0',
        ]);
    }

    public function test_api_allows_cors_preflight_requests(): void
    {
        $response = $this->withHeaders([
            'Origin' => 'https://meudominio.com',
            'Access-Control-Request-Method' => 'POST',
            'Access-Control-Request-Headers' => 'Content-Type',
        ])->options('/api/encrypt');

        $response->assertStatus(204);
        $response->assertHeader('Access-Control-Allow-Origin', '*');
    }
}

