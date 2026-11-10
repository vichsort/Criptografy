<?php

namespace Tests\Unit\Cryptography\Hill;

use App\Cryptography\Hill\Hill;
use App\Cryptography\Hill\HillException;
use PHPUnit\Framework\TestCase;

final class HillTest extends TestCase
{
    private Hill $hill;

    /** @var array{key: list<list<int>>} */
    private array $key2x2;

    protected function setUp(): void
    {
        $this->hill = new Hill;
        $this->key2x2 = ['key' => [[3, 3], [2, 5]]];
    }

    public function test_encrypts_a_known_2x2_example(): void
    {
        // Worked out by hand: det([[3,3],[2,5]]) = 9, gcd(9,26)=1 (invertible).
        $this->assertSame('HIAT', $this->hill->encrypt('HELP', $this->key2x2));
    }

    public function test_encrypts_the_classic_3x3_wikipedia_example(): void
    {
        $key = ['key' => [[6, 24, 1], [13, 16, 10], [20, 17, 15]]];

        $this->assertSame('POH', $this->hill->encrypt('ACT', $key));
    }

    public function test_decrypt_reverses_encrypt_when_length_is_a_multiple_of_block_size(): void
    {
        $plain = 'ATTACKATDAWN'; // 12 letters, a multiple of 2: no padding

        $cipher = $this->hill->encrypt($plain, $this->key2x2);
        $decrypted = $this->hill->decrypt($cipher, $this->key2x2);

        $this->assertSame($plain, $decrypted);
    }

    public function test_pads_the_last_incomplete_block_with_x(): void
    {
        $cipher = $this->hill->encrypt('HELLO', $this->key2x2); // 5 letters -> 6 with padding

        $this->assertSame(0, strlen($cipher) % 2);
        $this->assertSame('HELLOX', $this->hill->decrypt($cipher, $this->key2x2));
    }

    public function test_non_alphabetic_characters_are_stripped_not_preserved(): void
    {
        // A deliberate difference from Caesar/Vigenère; see the class docblock.
        $this->assertSame(
            $this->hill->encrypt('HELP', $this->key2x2),
            $this->hill->encrypt('He, Lp!', $this->key2x2),
        );
    }

    public function test_missing_key_throws(): void
    {
        $this->expectException(HillException::class);

        $this->hill->encrypt('HELP', []);
    }

    public function test_non_square_key_throws(): void
    {
        $this->expectException(HillException::class);

        $this->hill->encrypt('HELP', ['key' => [[1, 2, 3], [4, 5, 6]]]);
    }

    public function test_key_smaller_than_2x2_throws(): void
    {
        $this->expectException(HillException::class);

        $this->hill->encrypt('HELP', ['key' => [[5]]]);
    }

    public function test_non_invertible_key_throws(): void
    {
        // det([[2,4],[6,8]]) = -8 -> mod 26 = 18; gcd(18, 26) = 2 != 1.
        $this->expectException(HillException::class);

        $this->hill->encrypt('HELP', ['key' => [[2, 4], [6, 8]]]);
    }

    public function test_key_with_non_integer_values_throws(): void
    {
        $this->expectException(HillException::class);

        $this->hill->encrypt('HELP', ['key' => [[3, 3.5], [2, 5]]]);
    }

    public function test_key_value_with_repeated_minus_signs_throws(): void
    {
        // If '--1' were read as 0, det([[0,3],[1,5]]) = -3 ≡ 23 would be
        // invertible and the cipher would silently proceed with the wrong key.
        $this->expectException(HillException::class);
        $this->expectExceptionMessage('integers');

        $this->hill->encrypt('HELP', ['key' => [['--1', 3], [1, 5]]]);
    }

    public function test_key_value_string_beyond_integer_range_is_reduced_modulo_26(): void
    {
        // 99999999999999999999 mod 26 = 21; det([[21,3],[2,5]]) = 99 ≡ 21 (coprime with 26).
        $this->assertSame(
            $this->hill->encrypt('HELP', ['key' => [[21, 3], [2, 5]]]),
            $this->hill->encrypt('HELP', ['key' => [['99999999999999999999', 3], [2, 5]]]),
        );
    }

    public function test_large_integer_key_values_do_not_overflow_the_determinant(): void
    {
        // PHP_INT_MAX mod 26 = 7; det([[7,3],[2,5]]) = 29 ≡ 3 (coprime with 26).
        $key = ['key' => [[PHP_INT_MAX, 3], [2, 5]]];

        $this->assertSame(
            $this->hill->encrypt('HELP', ['key' => [[7, 3], [2, 5]]]),
            $this->hill->encrypt('HELP', $key),
        );
        $this->assertSame('HELP', $this->hill->decrypt($this->hill->encrypt('HELP', $key), $key));
    }

    public function test_float_key_value_beyond_exact_precision_throws(): void
    {
        // 1e20 cannot be represented exactly as an integer; accepting it would
        // mean encrypting with a value other than the one the client meant.
        $this->expectException(HillException::class);

        $this->hill->encrypt('HELP', ['key' => [[1e20, 3], [2, 5]]]);
    }

    public function test_decrypt_input_length_must_be_a_multiple_of_block_size(): void
    {
        $this->expectException(HillException::class);

        // "ABC" has 3 letters, but the block size is 2.
        $this->hill->decrypt('ABC', $this->key2x2);
    }

    public function test_empty_input_returns_empty_string(): void
    {
        $this->assertSame('', $this->hill->encrypt('', $this->key2x2));
        $this->assertSame('', $this->hill->decrypt('', $this->key2x2));
    }

    public function test_unicode_and_accented_characters_are_stripped(): void
    {
        // Hill works strictly on ASCII letters A-Z in blocks; symbols are dropped.
        $this->assertSame(
            $this->hill->encrypt('HELP', $this->key2x2),
            $this->hill->encrypt('H E L P ! 🚀', $this->key2x2),
        );

        // Accented (non-ASCII) letters are dropped; 'HéLp' becomes 'HLP', padded with 'X' => 'HLPX'
        $this->assertSame(
            $this->hill->encrypt('HLPX', $this->key2x2),
            $this->hill->encrypt('HéLp', $this->key2x2),
        );
    }

    public function test_describes_itself_with_stable_metadata(): void
    {
        $metadata = $this->hill->describe();

        $this->assertSame('hill', $metadata->id);
        $this->assertSame('encryption', $metadata->category);
        $this->assertTrue($metadata->requiresKey);
        $this->assertFalse($metadata->supportsHashing);
    }
}
