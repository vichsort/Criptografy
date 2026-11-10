<?php

namespace App\Cryptography\Caesar;

use App\Cryptography\Contracts\InvalidAlgorithmOptionsException;

final class CaesarException extends InvalidAlgorithmOptionsException
{
    public static function missingShift(): self
    {
        return new self('The "shift" option is required for the Caesar algorithm.');
    }

    public static function invalidShift(mixed $shift): self
    {
        $value = is_scalar($shift) ? (string) $shift : get_debug_type($shift);

        return new self(sprintf(
            'The "shift" option must be an integer. Received: %s',
            $value,
        ));
    }
}
