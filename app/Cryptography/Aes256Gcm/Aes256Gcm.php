<?php

namespace App\Cryptography\Aes256Gcm;

use App\Cryptography\Contracts\AlgorithmMetadata;
use App\Cryptography\Contracts\DescribesAlgorithm;
use App\Cryptography\Contracts\EncryptionAlgorithm;
use App\Cryptography\Contracts\OptionsSchema;

/**
 * AES-256-GCM: criptografia moderna autenticada.
 *
 * Ao contrário de Caesar/Vigenère/Hill, este módulo NÃO reimplementa o AES
 * (SubBytes, ShiftRows, MixColumns, AddRoundKey, ...) — isso seria risco sem
 * benefício para o produto (Seção 12). Toda a operação criptográfica em si
 * é delegada à extensão OpenSSL do PHP. A responsabilidade deste módulo é:
 *
 *     validar entrada -> validar/derivar chave -> gerar nonce ->
 *     chamar openssl_encrypt/openssl_decrypt -> montar/parsear o resultado
 *
 * Formato do resultado de encrypt() (e entrada esperada de decrypt()):
 * base64( nonce[12 bytes] . tag[16 bytes] . ciphertext ). Empacotar nonce e
 * tag junto ao ciphertext evita que o chamador precise gerenciá-los
 * separadamente — comum em ferramentas educacionais como esta.
 *
 * Nota sobre a chave: a chave informada pelo usuário (uma string qualquer)
 * é transformada em uma chave de 256 bits via SHA-256. Isso NÃO é uma KDF
 * apropriada para senhas reais (faltam salt e custo computacional, ex.:
 * Argon2id/PBKDF2) — é uma simplificação deliberada para que qualquer
 * string sirva como "chave" neste demonstrativo. Documentado aqui de
 * propósito, para não passar a falsa impressão de segurança de produção.
 */
final class Aes256Gcm implements EncryptionAlgorithm, DescribesAlgorithm
{
    private const CIPHER = 'aes-256-gcm';

    private const NONCE_LENGTH = 12; // 96 bits — recomendado para GCM

    private const TAG_LENGTH = 16; // 128 bits

    public function encrypt(string $input, array $options = []): string
    {
        $key = $this->resolveKey($options);
        $nonce = random_bytes(self::NONCE_LENGTH);
        $tag = '';

        $ciphertext = openssl_encrypt(
            $input,
            self::CIPHER,
            $key,
            OPENSSL_RAW_DATA,
            $nonce,
            $tag,
            '',
            self::TAG_LENGTH,
        );

        if ($ciphertext === false) {
            // Só ocorreria por um erro de ambiente (ex.: cipher indisponível),
            // já verificamos isso indiretamente ao construir a aplicação.
            throw new \RuntimeException('Unexpected failure while encrypting with AES-256-GCM.');
        }

        return base64_encode($nonce.$tag.$ciphertext);
    }

    public function decrypt(string $input, array $options = []): string
    {
        $key = $this->resolveKey($options);

        $raw = base64_decode($input, true);

        if ($raw === false || strlen($raw) < self::NONCE_LENGTH + self::TAG_LENGTH) {
            throw Aes256GcmException::invalidCiphertextFormat();
        }

        $nonce = substr($raw, 0, self::NONCE_LENGTH);
        $tag = substr($raw, self::NONCE_LENGTH, self::TAG_LENGTH);
        $ciphertext = substr($raw, self::NONCE_LENGTH + self::TAG_LENGTH);

        $plaintext = openssl_decrypt(
            $ciphertext,
            self::CIPHER,
            $key,
            OPENSSL_RAW_DATA,
            $nonce,
            $tag,
        );

        if ($plaintext === false) {
            throw Aes256GcmException::decryptionFailed();
        }

        return $plaintext;
    }

    /**
     * @param  array<string, mixed>  $options
     *
     * @throws Aes256GcmException
     */
    private function resolveKey(array $options): string
    {
        if (! array_key_exists('key', $options)) {
            throw Aes256GcmException::missingKey();
        }

        $key = $options['key'];

        if (! is_string($key) || $key === '') {
            throw Aes256GcmException::emptyKey();
        }

        // Deriva 32 bytes (256 bits) a partir de uma string de qualquer
        // tamanho. Ver nota de segurança no docblock da classe.
        return hash('sha256', $key, binary: true);
    }

    public function describe(): AlgorithmMetadata
    {
        return new AlgorithmMetadata(
            id: 'aes-256-gcm',
            name: 'AES-256-GCM',
            category: 'encryption',
            description: 'Modern authenticated encryption using the OpenSSL primitive (not reimplemented by hand). Combines confidentiality (AES-256) and integrity (GCM tag) in a single operation.',
            supportsEncryption: true,
            supportsDecryption: true,
            supportsHashing: false,
            requiresKey: true,
            securityLevel: 'modern',
            options: OptionsSchema::object(
                properties: [
                    'key' => [
                        'type' => 'string',
                        'title' => 'Key',
                        'description' => 'Any non-empty string. It is turned into a 256-bit key with SHA-256, a deliberate simplification: real systems should use a proper KDF.',
                        'minLength' => 1,
                    ],
                ],
                required: ['key'],
            ),
        );
    }
}
