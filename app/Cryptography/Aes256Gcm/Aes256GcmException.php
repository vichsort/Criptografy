<?php

namespace App\Cryptography\Aes256Gcm;

use App\Cryptography\Contracts\InvalidAlgorithmOptionsException;

final class Aes256GcmException extends InvalidAlgorithmOptionsException
{
    public static function missingKey(): self
    {
        return new self('The "key" option is required for the AES-256-GCM algorithm.');
    }

    public static function emptyKey(): self
    {
        return new self('The AES-256-GCM key must not be empty.');
    }

    public static function invalidCiphertextFormat(): self
    {
        return new self(
            'The ciphertext is not in a valid format (expected: base64 of nonce + tag + ciphertext).',
        );
    }

    public static function decryptionFailed(): self
    {
        return new self(
            'Decryption failed: wrong key or corrupted/tampered data '.
            '(the GCM authentication tag does not match).',
        );
    }
}
