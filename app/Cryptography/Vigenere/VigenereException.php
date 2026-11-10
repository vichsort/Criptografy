<?php

namespace App\Cryptography\Vigenere;

use App\Cryptography\Contracts\InvalidAlgorithmOptionsException;

final class VigenereException extends InvalidAlgorithmOptionsException
{
    public static function missingKey(): self
    {
        return new self('The "key" option is required for the Vigenère algorithm.');
    }

    public static function emptyKey(): self
    {
        return new self('The Vigenère key must not be empty.');
    }

    public static function invalidKey(mixed $key): self
    {
        $value = is_scalar($key) ? (string) $key : get_debug_type($key);

        return new self(sprintf(
            'The Vigenère key must contain only letters (A-Z). Received: "%s"',
            $value,
        ));
    }
}
