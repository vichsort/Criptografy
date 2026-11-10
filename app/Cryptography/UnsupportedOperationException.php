<?php

namespace App\Cryptography;

use RuntimeException;

final class UnsupportedOperationException extends RuntimeException
{
    public static function forOperation(string $algorithmId, string $operation): self
    {
        return new self(sprintf(
            'Algorithm "%s" does not support the "%s" operation.',
            $algorithmId,
            $operation,
        ));
    }
}
