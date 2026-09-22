<?php

namespace Tests\Unit\Cryptography\Sha512;

use App\Cryptography\Sha512\Sha512;
use PHPUnit\Framework\TestCase;

final class Sha512Test extends TestCase
{
    private Sha512 $sha512;

    protected function setUp(): void
    {
        $this->sha512 = new Sha512();
    }

    public function test_delegates_to_the_native_php_hash_function(): void
    {
        $this->assertSame(hash('sha512', 'abc'), $this->sha512->hash('abc'));
    }

    public function test_produces_a_128_character_hex_digest(): void
    {
        $digest = $this->sha512->hash('qualquer coisa');

        $this->assertSame(128, strlen($digest));
        $this->assertTrue(ctype_xdigit($digest));
    }

    public function test_describes_itself_as_hashing_without_encryption_support(): void
    {
        $metadata = $this->sha512->describe();

        $this->assertSame('sha-512', $metadata->id);
        $this->assertSame('hashing', $metadata->category);
        $this->assertTrue($metadata->supportsHashing);
        $this->assertFalse($metadata->supportsEncryption);
    }
}
