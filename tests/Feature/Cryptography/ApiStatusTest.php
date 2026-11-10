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
        // The API neither encrypts cookies nor uses sessions; a missing
        // APP_KEY must not take down the status endpoint.
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
        // The API stores no files; the storage/{path} routes (GET and PUT)
        // Laravel registers for the "local" disk have no use here.
        $this->assertFalse(Route::has('storage.local'));
        $this->assertFalse(Route::has('storage.local.upload'));
    }

    public function test_api_allows_cors_preflight_requests(): void
    {
        $response = $this->withHeaders([
            'Origin' => 'https://example.com',
            'Access-Control-Request-Method' => 'POST',
            'Access-Control-Request-Headers' => 'Content-Type',
        ])->options('/api/encrypt');

        $response->assertStatus(204);
        $response->assertHeader('Access-Control-Allow-Origin', '*');
    }
}
