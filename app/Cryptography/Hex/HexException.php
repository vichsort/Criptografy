<?php

namespace App\Cryptography\Hex;

use App\Cryptography\Contracts\InvalidAlgorithmOptionsException;

final class HexException extends InvalidAlgorithmOptionsException
{
    public static function invalidInput(): self
    {
        return new self(
            'O texto informado não é uma string hexadecimal válida '.
            '(deve conter apenas 0-9/a-f/A-F, em quantidade par de caracteres).',
        );
    }
}
