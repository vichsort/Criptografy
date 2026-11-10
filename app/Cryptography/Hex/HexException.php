<?php

namespace App\Cryptography\Hex;

use App\Cryptography\Contracts\InvalidAlgorithmOptionsException;

final class HexException extends InvalidAlgorithmOptionsException
{
    public static function invalidInput(): self
    {
        return new self(
            'The input is not a valid hexadecimal string '.
            '(it must contain only 0-9, a-f or A-F, with an even number of characters).',
        );
    }
}
