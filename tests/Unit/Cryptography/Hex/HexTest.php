<?php

namespace Tests\Unit\Cryptography\Hex;

use App\Cryptography\Hex\Hex;
use App\Cryptography\Hex\HexException;
use PHPUnit\Framework\TestCase;

final class HexTest extends TestCase
{
    private Hex $hex;

    protected function setUp(): void
    {
        $this->hex = new Hex;
    }

    public function test_encodes_a_known_string(): void
    {
        $this->assertSame('4142', $this->hex->encode('AB'));
    }

    public function test_decode_reverses_encode(): void
    {
        $original = 'texto qualquer 123';

        $this->assertSame($original, $this->hex->decode($this->hex->encode($original)));
    }

    public function test_decoding_non_hex_characters_throws(): void
    {
        $this->expectException(HexException::class);

        $this->hex->decode('zzz');
    }

    public function test_decoding_odd_length_string_throws(): void
    {
        $this->expectException(HexException::class);

        $this->hex->decode('abc');
    }

    public function test_decoding_empty_string_returns_empty_string(): void
    {
        $this->assertSame('', $this->hex->decode(''));
    }

    public function test_encodes_empty_string_returns_empty_string(): void
    {
        $this->assertSame('', $this->hex->encode(''));
    }

    public function test_handles_unicode_multibyte(): void
    {
        $original = 'café ☕ 123';

        $this->assertSame($original, $this->hex->decode($this->hex->encode($original)));
    }

    public function test_describes_itself_as_encoding_without_security_properties(): void
    {
        $metadata = $this->hex->describe();

        $this->assertSame('hex', $metadata->id);
        $this->assertSame('encoding', $metadata->category);
        $this->assertFalse($metadata->requiresKey);
    }
}
