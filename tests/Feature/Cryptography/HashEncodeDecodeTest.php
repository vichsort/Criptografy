<?php

namespace Tests\Feature\Cryptography;

use Tests\TestCase;

final class HashEncodeDecodeTest extends TestCase
{
    public function test_hashes_using_sha256(): void
    {
        $response = $this->postJson('/api/hash', [
            'algorithm' => 'sha-256',
            'input' => 'abc',
        ]);

        $response->assertOk();
        $response->assertJson(['result' => hash('sha256', 'abc')]);
    }

    public function test_hashes_using_sha512(): void
    {
        $response = $this->postJson('/api/hash', [
            'algorithm' => 'sha-512',
            'input' => 'abc',
        ]);

        $response->assertOk();
        $response->assertJson(['result' => hash('sha512', 'abc')]);
    }

    public function test_calling_encrypt_on_a_hash_algorithm_is_rejected(): void
    {
        // sha-256 não implementa EncryptionAlgorithm — o controller deve
        // recusar, não tentar adivinhar uma operação que o algoritmo não tem.
        $response = $this->postJson('/api/encrypt', [
            'algorithm' => 'sha-256',
            'input' => 'abc',
            'options' => [],
        ]);

        $response->assertStatus(422);
    }

    public function test_encodes_and_decodes_using_base64(): void
    {
        $encoded = $this->postJson('/api/encode', [
            'algorithm' => 'base64',
            'input' => 'Hello, World!',
        ]);

        $encoded->assertOk();
        $encoded->assertJson(['result' => 'SGVsbG8sIFdvcmxkIQ==']);

        $decoded = $this->postJson('/api/decode', [
            'algorithm' => 'base64',
            'input' => 'SGVsbG8sIFdvcmxkIQ==',
        ]);

        $decoded->assertOk();
        $decoded->assertJson(['result' => 'Hello, World!']);
    }

    public function test_encodes_and_decodes_using_hex(): void
    {
        $encoded = $this->postJson('/api/encode', [
            'algorithm' => 'hex',
            'input' => 'AB',
        ]);

        $encoded->assertOk();
        $encoded->assertJson(['result' => '4142']);

        $decoded = $this->postJson('/api/decode', [
            'algorithm' => 'hex',
            'input' => '4142',
        ]);

        $decoded->assertOk();
        $decoded->assertJson(['result' => 'AB']);
    }

    public function test_decoding_invalid_base64_returns_422(): void
    {
        $response = $this->postJson('/api/decode', [
            'algorithm' => 'base64',
            'input' => 'não é base64 válido !!!',
        ]);

        $response->assertStatus(422);
    }

    public function test_encrypts_and_decrypts_using_aes_256_gcm(): void
    {
        $encrypted = $this->postJson('/api/encrypt', [
            'algorithm' => 'aes-256-gcm',
            'input' => 'informação sensível',
            'options' => ['key' => 'minha-chave-secreta'],
        ]);

        $encrypted->assertOk();
        $cipher = $encrypted->json('result');
        $this->assertNotSame('informação sensível', $cipher);

        $decrypted = $this->postJson('/api/decrypt', [
            'algorithm' => 'aes-256-gcm',
            'input' => $cipher,
            'options' => ['key' => 'minha-chave-secreta'],
        ]);

        $decrypted->assertOk();
        $decrypted->assertJson(['result' => 'informação sensível']);
    }

    public function test_all_eight_algorithms_are_listed(): void
    {
        $response = $this->getJson('/api/algorithms');

        $response->assertOk();

        $ids = collect($response->json('data'))->pluck('id')->all();

        $this->assertEqualsCanonicalizing(
            ['caesar', 'vigenere', 'hill', 'aes-256-gcm', 'sha-256', 'sha-512', 'base64', 'hex'],
            $ids,
        );
    }

    public function test_hashes_empty_string_using_sha256(): void
    {
        $response = $this->postJson('/api/hash', [
            'algorithm' => 'sha-256',
            'input' => '',
        ]);

        $response->assertOk();
        $response->assertJson(['result' => 'e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855']);
    }

    public function test_encodes_and_decodes_empty_string_using_base64(): void
    {
        $encodeResponse = $this->postJson('/api/encode', [
            'algorithm' => 'base64',
            'input' => '',
        ]);

        $encodeResponse->assertOk();
        $encodeResponse->assertJson(['result' => '']);

        $decodeResponse = $this->postJson('/api/decode', [
            'algorithm' => 'base64',
            'input' => '',
        ]);

        $decodeResponse->assertOk();
        $decodeResponse->assertJson(['result' => '']);
    }

    public function test_unicode_encode_decode_roundtrip_through_http(): void
    {
        $payload = 'Café com bolo ☕ 100% 🚀';

        $encoded = $this->postJson('/api/encode', [
            'algorithm' => 'base64',
            'input' => $payload,
        ])->json('result');

        $decoded = $this->postJson('/api/decode', [
            'algorithm' => 'base64',
            'input' => $encoded,
        ]);

        $decoded->assertOk();
        $decoded->assertJson(['result' => $payload]);
    }
}
