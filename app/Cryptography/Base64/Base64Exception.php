<?php

namespace App\Cryptography\Base64;

use App\Cryptography\Contracts\InvalidAlgorithmOptionsException;

final class Base64Exception extends InvalidAlgorithmOptionsException
{
    public static function invalidInput(): self
    {
        return new self('The input is not a valid Base64 string.');
    }
}
