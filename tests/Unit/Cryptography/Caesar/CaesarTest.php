<?php

namespace Tests\Unit\Cryptography\Caesar;

use App\Cryptography\Caesar\Caesar;
use App\Cryptography\Caesar\CaesarException;
use PHPUnit\Framework\TestCase;

/**
 * Teste unitário puro: não depende do container do Laravel, apenas da
 * classe Caesar isolada. Isso o torna rápido e independente de HTTP/DB.
 */
final class CaesarTest extends TestCase
{
    private Caesar $caesar;

    protected function setUp(): void
    {
        $this->caesar = new Caesar();
    }

    public function test_encrypts_a_classic_phrase(): void
    {
        $this->assertSame(
            'Dwwdfn dw gdzq',
            $this->caesar->encrypt('Attack at dawn', ['shift' => 3]),
        );
    }

    public function test_decrypt_reverses_encrypt(): void
    {
        $plain = 'The quick brown fox jumps over the lazy dog';

        $cipher = $this->caesar->encrypt($plain, ['shift' => 17]);
        $decrypted = $this->caesar->decrypt($cipher, ['shift' => 17]);

        $this->assertSame($plain, $decrypted);
    }

    public function test_wraps_around_the_end_of_the_alphabet(): void
    {
        $this->assertSame('abc', $this->caesar->encrypt('xyz', ['shift' => 3]));
        $this->assertSame('ABC', $this->caesar->encrypt('XYZ', ['shift' => 3]));
    }

    public function test_shift_zero_is_the_identity(): void
    {
        $this->assertSame('abc', $this->caesar->encrypt('abc', ['shift' => 0]));
    }

    public function test_negative_shift_behaves_like_decrypting_with_the_positive_shift(): void
    {
        $this->assertSame(
            $this->caesar->decrypt('abc', ['shift' => 1]),
            $this->caesar->encrypt('abc', ['shift' => -1]),
        );
    }

    public function test_shift_larger_than_alphabet_size_wraps_via_modulo(): void
    {
        $this->assertSame(
            $this->caesar->encrypt('abc', ['shift' => 3]),
            $this->caesar->encrypt('abc', ['shift' => 29]),
        );
    }

    public function test_non_alphabetic_characters_are_preserved(): void
    {
        $this->assertSame(
            'Ifmmp, Xpsme! 123',
            $this->caesar->encrypt('Hello, World! 123', ['shift' => 1]),
        );
    }

    public function test_shift_as_numeric_string_is_accepted(): void
    {
        $this->assertSame(
            $this->caesar->encrypt('abc', ['shift' => 3]),
            $this->caesar->encrypt('abc', ['shift' => '3']),
        );
    }

    public function test_missing_shift_throws(): void
    {
        $this->expectException(CaesarException::class);
        $this->expectExceptionMessage('shift');

        $this->caesar->encrypt('abc', []);
    }

    public function test_non_numeric_shift_throws(): void
    {
        $this->expectException(CaesarException::class);

        $this->caesar->encrypt('abc', ['shift' => 'not-a-number']);
    }

    public function test_describes_itself_with_stable_metadata(): void
    {
        $metadata = $this->caesar->describe();

        $this->assertSame('caesar', $metadata->id);
        $this->assertSame('encryption', $metadata->category);
        $this->assertTrue($metadata->supportsEncryption);
        $this->assertTrue($metadata->supportsDecryption);
        $this->assertFalse($metadata->supportsHashing);
        $this->assertTrue($metadata->requiresKey);
    }
}
