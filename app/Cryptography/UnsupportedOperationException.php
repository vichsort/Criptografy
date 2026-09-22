<?php

namespace App\Cryptography;

use RuntimeException;

final class UnsupportedOperationException extends RuntimeException
{
    public static function forOperation(string $algorithmId, string $operation): self
    {
        return new self(sprintf(
            'O algoritmo "%s" não suporta a operação "%s".',
            $algorithmId,
            $operation,
        ));
    }
}
