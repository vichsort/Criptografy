<?php

namespace Tests\Unit\Cryptography\Base64;

use App\Cryptography\Base64\Base64;
use App\Cryptography\Base64\Base64Exception;
use PHPUnit\Framework\TestCase;

final class Base64Test extends TestCase
{
    private Base64 $base64;

    protected function setUp(): void
    {
        $this->base64 = new Base64();
    }

    public function test_encodes_a_known_string(): void
    {
        $this->assertSame('SGVsbG8sIFdvcmxkIQ==', $this->base64->encode('Hello, World!'));
    }

    public function test_decode_reverses_encode(): void
    {
        $original = 'café com açúcar 123 !@#';

        $this->assertSame($original, $this->base64->decode($this->base64->encode($original)));
    }

    public function test_decoding_invalid_base64_throws(): void
    {
        $this->expectException(Base64Exception::class);

        $this->base64->decode('isto não é base64 válido !!!');
    }

    public function test_encode_and_decode_empty_string(): void
    {
        $this->assertSame('', $this->base64->encode(''));
        $this->assertSame('', $this->base64->decode(''));
    }

    public function test_handles_emojis_and_multibyte(): void
    {
        $emoji = '🔑 Criptografia 🌐';

        $this->assertSame($emoji, $this->base64->decode($this->base64->encode($emoji)));
    }

    public function test_describes_itself_as_encoding_without_security_properties(): void
    {
        $metadata = $this->base64->describe();

        $this->assertSame('base64', $metadata->id);
        $this->assertSame('encoding', $metadata->category);
        $this->assertFalse($metadata->supportsEncryption);
        $this->assertFalse($metadata->requiresKey);
    }
}
