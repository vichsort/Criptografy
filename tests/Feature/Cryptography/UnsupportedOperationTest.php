<?php

namespace Tests\Feature\Cryptography;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Every operation endpoint checks that the resolved algorithm implements the
 * matching contract before calling it, so a mismatch is a clear 422 instead of
 * a call to a method the algorithm does not have (a 500).
 */
final class UnsupportedOperationTest extends TestCase
{
    /**
     * @return iterable<string, array{string, string}>
     */
    public static function mismatches(): iterable
    {
        yield 'encrypt with a hash' => ['encrypt', 'sha-256'];
        yield 'decrypt with a hash' => ['decrypt', 'sha-256'];
        yield 'hash with a cipher' => ['hash', 'caesar'];
        yield 'encode with a cipher' => ['encode', 'caesar'];
        yield 'decode with a hash' => ['decode', 'sha-512'];
    }

    #[DataProvider('mismatches')]
    public function test_operation_the_algorithm_does_not_implement_returns_422(string $operation, string $algorithm): void
    {
        $this->postJson("/api/{$operation}", [
            'algorithm' => $algorithm,
            'input' => 'abc',
            'options' => ['shift' => 3],
        ])
            ->assertStatus(422)
            ->assertExactJson([
                'message' => "Algorithm \"{$algorithm}\" does not support the \"{$operation}\" operation.",
            ]);
    }
}
