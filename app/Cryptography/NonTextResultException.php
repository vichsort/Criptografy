<?php

namespace App\Cryptography;

use RuntimeException;

/**
 * The algorithm produced a valid result, but its bytes are not UTF-8 text
 * (e.g. hex-decoding "ff", or decrypting a binary plaintext). The API answers
 * in JSON, which only carries UTF-8 text, so the result cannot be returned
 * without corrupting it.
 */
final class NonTextResultException extends RuntimeException
{
    public static function make(): self
    {
        return new self(
            'The operation result is not valid UTF-8 text (it contains binary bytes) '.
            'and cannot be represented in the JSON response.',
        );
    }
}
