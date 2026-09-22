<?php

namespace Tests\Unit\Cryptography\Vigenere;

use App\Cryptography\Vigenere\Vigenere;
use App\Cryptography\Vigenere\VigenereException;
use PHPUnit\Framework\TestCase;

final class VigenereTest extends TestCase
{
    private Vigenere $vigenere;

    protected function setUp(): void
    {
        $this->vigenere = new Vigenere();
    }

    public function test_encrypts_the_classic_lemon_example(): void
    {
        // Exemplo clássico de referência (Wikipedia): key=LEMON.
        $this->assertSame(
            'LXFOPVEFRNHR',
            $this->vigenere->encrypt('ATTACKATDAWN', ['key' => 'LEMON']),
        );
    }

    public function test_decrypt_reverses_encrypt(): void
    {
        $plain = 'The quick brown fox jumps over the lazy dog';

        $cipher = $this->vigenere->encrypt($plain, ['key' => 'secretkey']);
        $decrypted = $this->vigenere->decrypt($cipher, ['key' => 'secretkey']);

        $this->assertSame($plain, $decrypted);
    }

    public function test_key_case_does_not_affect_the_result(): void
    {
        $this->assertSame(
            $this->vigenere->encrypt('Attack', ['key' => 'LEMON']),
            $this->vigenere->encrypt('Attack', ['key' => 'lemon']),
        );
    }

    public function test_preserves_case_of_the_plaintext(): void
    {
        $cipher = $this->vigenere->encrypt('Attack', ['key' => 'LEMON']);

        // A 1ª letra do plaintext ("A" em "Attack") é maiúscula,
        // então a 1ª letra do cifrado também deve ser.
        $this->assertTrue(ctype_upper($cipher[0]));
    }

    public function test_non_alphabetic_characters_do_not_advance_the_key(): void
    {
        $withPunctuation = $this->vigenere->encrypt('Attack, at dawn!', ['key' => 'LEMON']);
        $onlyLetters = preg_replace('/[^A-Za-z]/', '', $withPunctuation);

        $expectedOnlyLetters = $this->vigenere->encrypt('Attackatdawn', ['key' => 'LEMON']);

        $this->assertSame($expectedOnlyLetters, $onlyLetters);
    }

    public function test_missing_key_throws(): void
    {
        $this->expectException(VigenereException::class);

        $this->vigenere->encrypt('abc', []);
    }

    public function test_empty_key_throws(): void
    {
        $this->expectException(VigenereException::class);

        $this->vigenere->encrypt('abc', ['key' => '']);
    }

    public function test_key_with_non_alphabetic_characters_throws(): void
    {
        $this->expectException(VigenereException::class);

        $this->vigenere->encrypt('abc', ['key' => 'LE MON123']);
    }

    public function test_empty_input_returns_empty_string(): void
    {
        $this->assertSame('', $this->vigenere->encrypt('', ['key' => 'SECRET']));
        $this->assertSame('', $this->vigenere->decrypt('', ['key' => 'SECRET']));
    }

    public function test_unicode_and_accented_characters_are_preserved(): void
    {
        $plain = 'Mensagem com acentuação: café & pão! 🔒';
        $encrypted = $this->vigenere->encrypt($plain, ['key' => 'CHAVE']);
        $decrypted = $this->vigenere->decrypt($encrypted, ['key' => 'CHAVE']);

        $this->assertSame($plain, $decrypted);
        $this->assertStringContainsString('é', $encrypted);
        $this->assertStringContainsString('ã', $encrypted);
        $this->assertStringContainsString('🔒', $encrypted);
    }

    public function test_describes_itself_with_stable_metadata(): void
    {
        $metadata = $this->vigenere->describe();

        $this->assertSame('vigenere', $metadata->id);
        $this->assertSame('encryption', $metadata->category);
        $this->assertTrue($metadata->requiresKey);
        $this->assertFalse($metadata->supportsHashing);
    }
}
