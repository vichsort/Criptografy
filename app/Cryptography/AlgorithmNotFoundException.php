<?php

namespace App\Cryptography;

use RuntimeException;

final class AlgorithmNotFoundException extends RuntimeException
{
    public function __construct(string $id)
    {
        parent::__construct(sprintf('Algorithm "%s" does not exist.', $id));
    }
}
