<?php

namespace App\Cryptography;

use RuntimeException;

/**
 * O algoritmo produziu um resultado válido, mas em bytes que não formam
 * texto UTF-8 (ex.: decode de hex "ff", ou decrypt de um plaintext binário).
 * A API responde em JSON, que só transporta texto UTF-8, então o resultado
 * não pode ser devolvido sem ser corrompido.
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
