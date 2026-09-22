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
        $this->hill = new Hill();
        $this->key2x2 = ['key' => [[3, 3], [2, 5]]];
    }

    public function test_encrypts_a_known_2x2_example(): void
    {
        // Calculado manualmente: det([[3,3],[2,5]]) = 9, mdc(9,26)=1 (inversível).
        $this->assertSame('HIAT', $this->hill->encrypt('HELP', $this->key2x2));
    }

    public function test_encrypts_the_classic_3x3_wikipedia_example(): void
    {
        $key = ['key' => [[6, 24, 1], [13, 16, 10], [20, 17, 15]]];

        $this->assertSame('POH', $this->hill->encrypt('ACT', $key));
    }

    public function test_decrypt_reverses_encrypt_when_length_is_a_multiple_of_block_size(): void
    {
        $plain = 'ATTACKATDAWN'; // 12 letras, múltiplo de 2 — sem padding

        $cipher = $this->hill->encrypt($plain, $this->key2x2);
        $decrypted = $this->hill->decrypt($cipher, $this->key2x2);

        $this->assertSame($plain, $decrypted);
    }

    public function test_pads_the_last_incomplete_block_with_x(): void
    {
        $cipher = $this->hill->encrypt('HELLO', $this->key2x2); // 5 letras -> 6 com padding

        $this->assertSame(0, strlen($cipher) % 2);
        $this->assertSame('HELLOX', $this->hill->decrypt($cipher, $this->key2x2));
    }

    public function test_non_alphabetic_characters_are_stripped_not_preserved(): void
    {
        // Diferença deliberada em relação a Caesar/Vigenère — ver docblock da classe.
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
        // det([[2,4],[6,8]]) = -8 -> mod 26 = 18; mdc(18, 26) = 2 != 1.
        $this->expectException(HillException::class);

        $this->hill->encrypt('HELP', ['key' => [[2, 4], [6, 8]]]);
    }

    public function test_key_with_non_integer_values_throws(): void
    {
        $this->expectException(HillException::class);

        $this->hill->encrypt('HELP', ['key' => [[3, 3.5], [2, 5]]]);
    }

    public function test_decrypt_input_length_must_be_a_multiple_of_block_size(): void
    {
        $this->expectException(HillException::class);

        // "ABC" tem 3 letras, mas o bloco é 2.
        $this->hill->decrypt('ABC', $this->key2x2);
    }

    public function test_empty_input_returns_empty_string(): void
    {
        $this->assertSame('', $this->hill->encrypt('', $this->key2x2));
        $this->assertSame('', $this->hill->decrypt('', $this->key2x2));
    }

    public function test_unicode_and_accented_characters_are_stripped(): void
    {
        // Hill opera estritamente com letras ASCII A-Z em blocos; símbolos são descartados.
        $this->assertSame(
            $this->hill->encrypt('HELP', $this->key2x2),
            $this->hill->encrypt('H E L P ! 🚀', $this->key2x2),
        );

        // Acentos (não-ASCII) são descartados; 'HéLp' vira 'HLP' que recebe padding 'X' => 'HLPX'
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
