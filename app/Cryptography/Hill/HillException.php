<?php

namespace App\Cryptography\Hill;

use App\Cryptography\Contracts\InvalidAlgorithmOptionsException;

final class HillException extends InvalidAlgorithmOptionsException
{
    public static function missingKey(): self
    {
        return new self('The "key" option (a matrix) is required for the Hill algorithm.');
    }

    public static function invalidKeyFormat(): self
    {
        return new self(
            'The Hill key must be a square matrix (an array of arrays) of integers. '.
            'Example: [[3, 3], [2, 5]].',
        );
    }

    public static function keyTooSmall(): self
    {
        return new self('The Hill key matrix must be at least 2x2.');
    }

    public static function keyTooLarge(int $maxSize): self
    {
        return new self(sprintf(
            'The Hill key matrix cannot be larger than %dx%d, due to practical processing limits.',
            $maxSize,
            $maxSize,
        ));
    }

    public static function keyNotInvertible(int $determinant, int $modulus): self
    {
        return new self(sprintf(
            'The key matrix is not invertible modulo %d: det(K) = %d and gcd(%d, %d) != 1. '.
            'Choose a matrix whose determinant is coprime with %d.',
            $modulus,
            $determinant,
            $determinant,
            $modulus,
            $modulus,
        ));
    }

    public static function inputLengthNotMultipleOfBlockSize(int $blockSize): self
    {
        return new self(sprintf(
            'The ciphertext must contain a number of letters that is a multiple of the block size (%d).',
            $blockSize,
        ));
    }
}
