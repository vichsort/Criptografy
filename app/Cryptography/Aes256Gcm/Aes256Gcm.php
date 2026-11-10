<?php

namespace App\Cryptography\Aes256Gcm;

use App\Cryptography\Contracts\AlgorithmMetadata;
use App\Cryptography\Contracts\DescribesAlgorithm;
use App\Cryptography\Contracts\EncryptionAlgorithm;
use App\Cryptography\Contracts\OptionsSchema;

/**
 * AES-256-GCM: modern authenticated encryption.
 *
 * Unlike Caesar/Vigenère/Hill, this module does NOT reimplement AES
 * (SubBytes, ShiftRows, MixColumns, AddRoundKey, ...): that would add risk
 * with no benefit. The cryptographic operation itself is delegated to PHP's
 * OpenSSL extension. This module is responsible for:
 *
 *     validate input -> validate/derive key -> generate nonce ->
 *     call openssl_encrypt/openssl_decrypt -> build/parse the result
 *
 * Format of encrypt()'s output (and of decrypt()'s expected input):
 * base64( nonce[12 bytes] . tag[16 bytes] . ciphertext ). Packing the nonce
 * and tag with the ciphertext spares the caller from managing them
 * separately, which is common in educational tools like this one.
 *
 * Note on the key: the user-supplied key (any string) is turned into a
 * 256-bit key with SHA-256. This is NOT a proper KDF for real passwords (it
 * lacks a salt and a work factor, e.g. Argon2id/PBKDF2); it is a deliberate
 * simplification so any string can serve as a "key" in this demo. It is
 * documented here on purpose, so it does not give a false impression of
 * production-grade security.
 */
final class Aes256Gcm implements DescribesAlgorithm, EncryptionAlgorithm
{
    private const CIPHER = 'aes-256-gcm';

    private const NONCE_LENGTH = 12; // 96 bits, the recommended size for GCM

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
            // Only happens on an environment error (e.g. the cipher is
            // unavailable), which bootstrapping the application already
            // checks indirectly.
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

        // Derives 32 bytes (256 bits) from a string of any length. See the
        // security note in the class docblock.
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
