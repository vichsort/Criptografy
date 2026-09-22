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
        $this->aes = new Aes256Gcm();
    }

    public function test_decrypt_reverses_encrypt_with_the_same_key(): void
    {
        $plain = 'Segredo de estado';
        $options = ['key' => 'senha-super-secreta'];

        $cipher = $this->aes->encrypt($plain, $options);

        $this->assertNotSame($plain, $cipher);
        $this->assertSame($plain, $this->aes->decrypt($cipher, $options));
    }

    public function test_encrypting_the_same_plaintext_twice_yields_different_ciphertexts(): void
    {
        // Propriedade de segurança: o nonce é aleatório a cada chamada.
        $options = ['key' => 'senha-super-secreta'];

        $first = $this->aes->encrypt('mesmo texto', $options);
        $second = $this->aes->encrypt('mesmo texto', $options);

        $this->assertNotSame($first, $second);
    }

    public function test_decrypting_with_the_wrong_key_fails_authentication(): void
    {
        $cipher = $this->aes->encrypt('dado sensível', ['key' => 'chave-correta']);

        $this->expectException(Aes256GcmException::class);

        $this->aes->decrypt($cipher, ['key' => 'chave-errada']);
    }

    public function test_decrypting_tampered_ciphertext_fails_authentication(): void
    {
        $cipher = $this->aes->encrypt('dado sensível', ['key' => 'chave']);
        $lastChar = $cipher[-1];
        $tampered = substr($cipher, 0, -1).($lastChar === 'A' ? 'B' : 'A');

        $this->expectException(Aes256GcmException::class);

        $this->aes->decrypt($tampered, ['key' => 'chave']);
    }

    public function test_missing_key_throws(): void
    {
        $this->expectException(Aes256GcmException::class);

        $this->aes->encrypt('x', []);
    }

    public function test_decrypting_malformed_ciphertext_throws(): void
    {
        $this->expectException(Aes256GcmException::class);

        $this->aes->decrypt('nao-e-base64-nem-de-longe!!', ['key' => 'chave']);
    }

    public function test_handles_empty_input_string(): void
    {
        $options = ['key' => 'minha-chave'];
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
        $options = ['key' => 'chave-teste'];
        $plain = str_repeat('Bloco de 64 bytes para teste de volume criptográfico em memória! ', 1000); // ~64 KB
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
