<?php

namespace Tests\Unit\Cryptography\Aes256Gcm;

use App\Cryptography\Aes256Gcm\Aes256Gcm;
use App\Cryptography\Aes256Gcm\Aes256GcmException;
use PHPUnit\Framework\TestCase;

final class Aes256GcmTest extends TestCase
{
    private Aes256Gcm $aes;

    protected function setUp(): void
    {
        $this->aes = new Aes256Gcm;
    }

    public function test_decrypt_reverses_encrypt_with_the_same_key(): void
    {
        $plain = 'State secret';
        $options = ['key' => 'super-secret-password'];

        $cipher = $this->aes->encrypt($plain, $options);

        $this->assertNotSame($plain, $cipher);
        $this->assertSame($plain, $this->aes->decrypt($cipher, $options));
    }

    public function test_encrypting_the_same_plaintext_twice_yields_different_ciphertexts(): void
    {
        // Security property: the nonce is random on every call.
        $options = ['key' => 'super-secret-password'];

        $first = $this->aes->encrypt('same text', $options);
        $second = $this->aes->encrypt('same text', $options);

        $this->assertNotSame($first, $second);
    }

    public function test_decrypting_with_the_wrong_key_fails_authentication(): void
    {
        $cipher = $this->aes->encrypt('sensitive data', ['key' => 'right-key']);

        $this->expectException(Aes256GcmException::class);

        $this->aes->decrypt($cipher, ['key' => 'wrong-key']);
    }

    public function test_decrypting_tampered_ciphertext_fails_authentication(): void
    {
        $cipher = $this->aes->encrypt('sensitive data', ['key' => 'key']);
        $lastChar = $cipher[-1];
        $tampered = substr($cipher, 0, -1).($lastChar === 'A' ? 'B' : 'A');

        $this->expectException(Aes256GcmException::class);

        $this->aes->decrypt($tampered, ['key' => 'key']);
    }

    public function test_missing_key_throws(): void
    {
        $this->expectException(Aes256GcmException::class);

        $this->aes->encrypt('x', []);
    }

    public function test_decrypting_malformed_ciphertext_throws(): void
    {
        $this->expectException(Aes256GcmException::class);

        $this->aes->decrypt('not-even-close-to-base64!!', ['key' => 'key']);
    }

    public function test_decrypting_input_shorter_than_nonce_and_tag_throws_a_format_error(): void
    {
        // Valid base64, but 27 bytes cannot hold a 12-byte nonce plus a
        // 16-byte tag: a format error, not an authentication failure.
        $this->expectExceptionObject(Aes256GcmException::invalidCiphertextFormat());

        $this->aes->decrypt(base64_encode(str_repeat("\0", 27)), ['key' => 'key']);
    }

    public function test_handles_empty_input_string(): void
    {
        $options = ['key' => 'my-key'];
        $cipher = $this->aes->encrypt('', $options);

        $this->assertNotEmpty($cipher);
        $this->assertSame('', $this->aes->decrypt($cipher, $options));
    }

    public function test_handles_unicode_and_emojis(): void
    {
        $options = ['key' => 'chave-com-acentuação-🔑'];
        $plain = 'Mensagem com símbolos complexos: 日本語, 🔐, äëïöü!';
        $cipher = $this->aes->encrypt($plain, $options);

        $this->assertSame($plain, $this->aes->decrypt($cipher, $options));
    }

    public function test_handles_large_data(): void
    {
        $options = ['key' => 'test-key'];
        $plain = str_repeat('A block of text used to check encryption of a large payload!!! ', 1000); // ~64 KB
        $cipher = $this->aes->encrypt($plain, $options);

        $this->assertSame($plain, $this->aes->decrypt($cipher, $options));
    }

    public function test_describes_itself_with_stable_metadata(): void
    {
        $metadata = $this->aes->describe();

        $this->assertSame('aes-256-gcm', $metadata->id);
        $this->assertSame('modern', $metadata->securityLevel);
        $this->assertTrue($metadata->requiresKey);
    }
}
