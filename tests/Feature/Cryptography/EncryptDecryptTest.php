<?php

namespace Tests\Feature\Cryptography;

use Tests\TestCase;

final class EncryptDecryptTest extends TestCase
{
    public function test_encrypts_using_caesar(): void
    {
        $response = $this->postJson('/api/encrypt', [
            'algorithm' => 'caesar',
            'input' => 'Attack at dawn',
            'options' => ['shift' => 3],
        ]);

        $response->assertOk();
        $response->assertJson(['result' => 'Dwwdfn dw gdzq']);
    }

    public function test_decrypt_reverses_encrypt_through_the_http_layer(): void
    {
        $encrypted = $this->postJson('/api/encrypt', [
            'algorithm' => 'caesar',
            'input' => 'Meet me at midnight',
            'options' => ['shift' => 11],
        ])->json('result');

        $decrypted = $this->postJson('/api/decrypt', [
            'algorithm' => 'caesar',
            'input' => $encrypted,
            'options' => ['shift' => 11],
        ]);

        $decrypted->assertOk();
        $decrypted->assertJson(['result' => 'Meet me at midnight']);
    }

    public function test_encrypts_using_vigenere(): void
    {
        $response = $this->postJson('/api/encrypt', [
            'algorithm' => 'vigenere',
            'input' => 'ATTACKATDAWN',
            'options' => ['key' => 'LEMON'],
        ]);

        $response->assertOk();
        $response->assertJson(['result' => 'LXFOPVEFRNHR']);
    }

    public function test_missing_key_option_returns_422_for_vigenere(): void
    {
        $response = $this->postJson('/api/encrypt', [
            'algorithm' => 'vigenere',
            'input' => 'hello',
            'options' => [],
        ]);

        $response->assertStatus(422);
    }

    public function test_encrypts_using_hill(): void
    {
        $response = $this->postJson('/api/encrypt', [
            'algorithm' => 'hill',
            'input' => 'HELP',
            'options' => ['key' => [[3, 3], [2, 5]]],
        ]);

        $response->assertOk();
        $response->assertJson(['result' => 'HIAT']);
    }

    public function test_non_invertible_hill_key_returns_422(): void
    {
        $response = $this->postJson('/api/encrypt', [
            'algorithm' => 'hill',
            'input' => 'HELP',
            'options' => ['key' => [[2, 4], [6, 8]]],
        ]);

        $response->assertStatus(422);
    }

    public function test_unknown_algorithm_returns_404(): void
    {
        $response = $this->postJson('/api/encrypt', [
            'algorithm' => 'does-not-exist',
            'input' => 'hello',
            'options' => [],
        ]);

        $response->assertStatus(404);
    }

    public function test_missing_shift_option_returns_422_with_message(): void
    {
        $response = $this->postJson('/api/encrypt', [
            'algorithm' => 'caesar',
            'input' => 'hello',
            'options' => [],
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('message', fn (string $message) => str_contains($message, 'shift'));
    }

    public function test_missing_input_fails_request_validation(): void
    {
        $response = $this->postJson('/api/encrypt', [
            'algorithm' => 'caesar',
            'options' => ['shift' => 3],
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('input');
    }

    public function test_missing_algorithm_fails_request_validation(): void
    {
        $response = $this->postJson('/api/encrypt', [
            'input' => 'hello',
            'options' => ['shift' => 3],
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('algorithm');
    }

    public function test_null_input_fails_request_validation(): void
    {
        $response = $this->postJson('/api/encrypt', [
            'algorithm' => 'caesar',
            'input' => null,
            'options' => ['shift' => 3],
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('input');
    }

    public function test_empty_string_input_is_accepted(): void
    {
        $response = $this->postJson('/api/encrypt', [
            'algorithm' => 'caesar',
            'input' => '',
            'options' => ['shift' => 3],
        ]);

        $response->assertOk();
        $response->assertJson(['result' => '']);
    }

    public function test_unicode_payload_roundtrip_through_http(): void
    {
        $payload = 'Atenção 123! Chave secreta: 🔑';

        $encrypted = $this->postJson('/api/encrypt', [
            'algorithm' => 'aes-256-gcm',
            'input' => $payload,
            'options' => ['key' => 'senha-forte'],
        ])->json('result');

        $decrypted = $this->postJson('/api/decrypt', [
            'algorithm' => 'aes-256-gcm',
            'input' => $encrypted,
            'options' => ['key' => 'senha-forte'],
        ]);

        $decrypted->assertOk();
        $decrypted->assertJson(['result' => $payload]);
    }
}
