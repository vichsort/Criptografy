<?php

namespace Tests\Unit\Cryptography\Sha256;

use App\Cryptography\Sha256\Sha256;
use PHPUnit\Framework\TestCase;

final class Sha256Test extends TestCase
{
    private Sha256 $sha256;

    protected function setUp(): void
    {
        $this->sha256 = new Sha256;
    }

    public function test_delegates_to_the_native_php_hash_function(): void
    {
        // Não reimplementamos SHA-256 (Seção 13) — a garantia de correção
        // vem de delegar para a implementação nativa do PHP.
        $this->assertSame(hash('sha256', 'abc'), $this->sha256->hash('abc'));
    }

    public function test_produces_a_64_character_hex_digest(): void
    {
        $digest = $this->sha256->hash('qualquer coisa');

        $this->assertSame(64, strlen($digest));
        $this->assertTrue(ctype_xdigit($digest));
    }

    public function test_is_deterministic(): void
    {
        $this->assertSame(
            $this->sha256->hash('mesma entrada'),
            $this->sha256->hash('mesma entrada'),
        );
    }

    public function test_different_inputs_produce_different_digests(): void
    {
        $this->assertNotSame(
            $this->sha256->hash('a'),
            $this->sha256->hash('b'),
        );
    }

    public function test_empty_string_produces_nist_empty_digest(): void
    {
        // Vetor padrão NIST para string vazia
        $this->assertSame(
            'e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855',
            $this->sha256->hash(''),
        );
    }

    public function test_hashes_unicode_characters(): void
    {
        $this->assertSame(
            hash('sha256', 'Café & Pão 🚀'),
            $this->sha256->hash('Café & Pão 🚀'),
        );
    }

    public function test_describes_itself_as_hashing_without_encryption_support(): void
    {
        $metadata = $this->sha256->describe();

        $this->assertSame('sha-256', $metadata->id);
        $this->assertSame('hashing', $metadata->category);
        $this->assertTrue($metadata->supportsHashing);
        $this->assertFalse($metadata->supportsEncryption);
        $this->assertFalse($metadata->supportsDecryption);
        $this->assertFalse($metadata->requiresKey);
    }
}
