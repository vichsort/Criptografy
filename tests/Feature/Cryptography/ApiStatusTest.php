<?php

namespace Tests\Feature\Cryptography;

use Illuminate\Support\Facades\Route;
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

    public function test_root_works_without_application_key(): void
    {
        // A API não criptografa cookies nem usa sessão; a falta de APP_KEY
        // não pode derrubar o endpoint de status.
        config(['app.key' => null]);

        $this->getJson('/')->assertOk();
    }

    public function test_root_does_not_start_a_session(): void
    {
        $response = $this->getJson('/');

        $response->assertOk();
        $this->assertFalse($response->headers->has('Set-Cookie'));
    }

    public function test_local_storage_files_are_not_served_over_http(): void
    {
        // A API não armazena arquivos; as rotas storage/{path} (GET e PUT)
        // que o Laravel registra para o disco "local" não têm uso aqui.
        $this->assertFalse(Route::has('storage.local'));
        $this->assertFalse(Route::has('storage.local.upload'));
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

