<?php

namespace Tests\Unit\Cryptography\Caesar;

use App\Cryptography\Caesar\Caesar;
use App\Cryptography\Caesar\CaesarException;
use PHPUnit\Framework\TestCase;

/**
 * Pure unit test: it depends only on the isolated Caesar class, not on
 * Laravel's container, which keeps it fast and independent of HTTP/DB.
 */
final class CaesarTest extends TestCase
{
    private Caesar $caesar;

    protected function setUp(): void
    {
        $this->caesar = new Caesar;
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

    public function test_shift_as_integral_float_is_accepted(): void
    {
        // JSON "3.0" arrives as a float; Hill already accepted it, and so does Caesar now.
        $this->assertSame(
            $this->caesar->encrypt('abc', ['shift' => 3]),
            $this->caesar->encrypt('abc', ['shift' => 3.0]),
        );
    }

    public function test_fractional_shift_throws(): void
    {
        $this->expectException(CaesarException::class);

        $this->caesar->encrypt('abc', ['shift' => 3.5]);
    }

    public function test_shift_with_repeated_minus_signs_throws(): void
    {
        $this->expectException(CaesarException::class);

        $this->caesar->encrypt('abc', ['shift' => '--3']);
    }

    public function test_shift_string_beyond_integer_range_wraps_via_modulo(): void
    {
        // 99999999999999999999 mod 26 = 21
        $this->assertSame(
            $this->caesar->encrypt('abc', ['shift' => 21]),
            $this->caesar->encrypt('abc', ['shift' => '99999999999999999999']),
        );
    }

    public function test_decrypt_with_minimum_integer_shift_does_not_overflow(): void
    {
        // PHP_INT_MIN mod 26 = 18; negating PHP_INT_MIN without reducing it first overflows to float.
        $this->assertSame(
            $this->caesar->decrypt('abc', ['shift' => 18]),
            $this->caesar->decrypt('abc', ['shift' => PHP_INT_MIN]),
        );
    }

    public function test_empty_input_returns_empty_string(): void
    {
        $this->assertSame('', $this->caesar->encrypt('', ['shift' => 5]));
        $this->assertSame('', $this->caesar->decrypt('', ['shift' => 5]));
    }

    public function test_unicode_and_accented_characters_are_preserved(): void
    {
        $plain = 'Olá, mundo! Criptografia avançada: 100% 🚀';
        $encrypted = $this->caesar->encrypt($plain, ['shift' => 3]);
        $decrypted = $this->caesar->decrypt($encrypted, ['shift' => 3]);

        $this->assertSame($plain, $decrypted);
        $this->assertStringContainsString('á', $encrypted);
        $this->assertStringContainsString('ç', $encrypted);
        $this->assertStringContainsString('🚀', $encrypted);
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
