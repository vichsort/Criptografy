<?php

namespace Tests\Feature\Cryptography;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Tudo o que a API expõe (mensagens de erro e metadados dos algoritmos) é
 * em inglês, o idioma padrão do sistema.
 */
final class ExposedLanguageTest extends TestCase
{
    /**
     * @return iterable<string, array{string, array<string, mixed>, string}>
     */
    public static function errorMessages(): iterable
    {
        yield 'algorithm not found' => [
            '/api/encrypt', ['algorithm' => 'nope', 'input' => 'a'],
            'Algorithm "nope" does not exist.',
        ];
        yield 'unsupported operation' => [
            '/api/encrypt', ['algorithm' => 'sha-256', 'input' => 'a'],
            'Algorithm "sha-256" does not support the "encrypt" operation.',
        ];
        yield 'non-text result' => [
            '/api/decode', ['algorithm' => 'hex', 'input' => 'ff'],
            'The operation result is not valid UTF-8 text (it contains binary bytes) and cannot be represented in the JSON response.',
        ];
        yield 'caesar missing shift' => [
            '/api/encrypt', ['algorithm' => 'caesar', 'input' => 'a'],
            'The "shift" option is required for the Caesar algorithm.',
        ];
        yield 'caesar invalid shift' => [
            '/api/encrypt', ['algorithm' => 'caesar', 'input' => 'a', 'options' => ['shift' => '--3']],
            'The "shift" option must be an integer. Received: --3',
        ];
        yield 'vigenere missing key' => [
            '/api/encrypt', ['algorithm' => 'vigenere', 'input' => 'a'],
            'The "key" option is required for the Vigenère algorithm.',
        ];
        yield 'vigenere invalid key' => [
            '/api/encrypt', ['algorithm' => 'vigenere', 'input' => 'a', 'options' => ['key' => 'abc1']],
            'The Vigenère key must contain only letters (A-Z). Received: "abc1"',
        ];
        yield 'hill invalid key format' => [
            '/api/encrypt', ['algorithm' => 'hill', 'input' => 'a', 'options' => ['key' => 'abc']],
            'The Hill key must be a square matrix (an array of arrays) of integers. Example: [[3, 3], [2, 5]].',
        ];
        yield 'hill non-invertible key' => [
            '/api/encrypt', ['algorithm' => 'hill', 'input' => 'a', 'options' => ['key' => [[2, 4], [6, 8]]]],
            'The key matrix is not invertible modulo 26: det(K) = 18 and gcd(18, 26) != 1. Choose a matrix whose determinant is coprime with 26.',
        ];
        yield 'aes missing key' => [
            '/api/encrypt', ['algorithm' => 'aes-256-gcm', 'input' => 'a'],
            'The "key" option is required for the AES-256-GCM algorithm.',
        ];
        yield 'aes decryption failed' => [
            '/api/decrypt', ['algorithm' => 'aes-256-gcm', 'input' => base64_encode(str_repeat('x', 40)), 'options' => ['key' => 'k']],
            'Decryption failed: wrong key or corrupted/tampered data (the GCM authentication tag does not match).',
        ];
        yield 'base64 invalid input' => [
            '/api/decode', ['algorithm' => 'base64', 'input' => '***'],
            'The input is not a valid Base64 string.',
        ];
        yield 'hex invalid input' => [
            '/api/decode', ['algorithm' => 'hex', 'input' => 'xyz'],
            'The input is not a valid hexadecimal string (it must contain only 0-9, a-f or A-F, with an even number of characters).',
        ];
    }

    /**
     * @param  array<string, mixed>  $body
     */
    #[DataProvider('errorMessages')]
    public function test_error_messages_are_in_english(string $uri, array $body, string $expected): void
    {
        $this->postJson($uri, $body)->assertJsonPath('message', $expected);
    }

    public function test_framework_validation_messages_are_in_english(): void
    {
        $this->postJson('/api/encrypt', ['input' => 'a'])
            ->assertJsonPath('errors.algorithm.0', 'The algorithm field is required.');
    }

    public function test_algorithm_descriptions_are_in_english(): void
    {
        // Descrições em inglês não precisam de acentos; um caractere não
        // ASCII aqui quase sempre é texto em português que ficou para trás.
        $descriptions = collect($this->getJson('/api/algorithms')->json('data'))
            ->pluck('description', 'id');

        $this->assertNotEmpty($descriptions);

        foreach ($descriptions as $id => $description) {
            $this->assertTrue(
                mb_check_encoding($description, 'ASCII'),
                "Description of \"{$id}\" contains non-ASCII text: {$description}",
            );
        }
    }

    public function test_option_titles_and_descriptions_are_in_english(): void
    {
        $texts = collect($this->getJson('/api/algorithms')->json('data'))
            ->flatMap(fn (array $algorithm) => collect($algorithm['options']['properties'] ?? [])
                ->flatMap(fn (array $option, string $name) => [
                    "{$algorithm['id']}.{$name}.title" => $option['title'] ?? '',
                    "{$algorithm['id']}.{$name}.description" => $option['description'] ?? '',
                ]));

        $this->assertNotEmpty($texts);

        foreach ($texts as $path => $text) {
            $this->assertTrue(mb_check_encoding($text, 'ASCII'), "{$path} contains non-ASCII text: {$text}");
        }
    }
}
