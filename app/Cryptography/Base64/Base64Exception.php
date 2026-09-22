<?php

namespace App\Cryptography\Base64;

use App\Cryptography\Contracts\InvalidAlgorithmOptionsException;

final class Base64Exception extends InvalidAlgorithmOptionsException
{
    public static function invalidInput(): self
    {
        return new self('O texto informado não é uma string Base64 válida.');
    }
}
