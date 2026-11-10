<?php

namespace App\Cryptography\Support;

/**
 * Reads a client-supplied value as an integer and returns its residue
 * modulo N, in the range [0, N).
 *
 * Classical ciphers (Caesar, Hill) only depend on their parameters mod 26.
 * Reducing them as soon as they are read avoids two problems:
 *
 *  - overflow: arithmetic on values near PHP_INT_MAX/MIN turns into float
 *    and breaks functions typed as int;
 *  - silent value loss: `(int) "99999999999999999999"` saturates at
 *    PHP_INT_MAX. Here strings of any length are reduced digit by digit,
 *    without ever materializing the whole number.
 *
 * Accepts: int; float holding an exact integer value (|v| <= 2^53 - 1, e.g. JSON
 * "3.0"); string matching /^-?\d+$/. Anything else (including "--3", "+3",
 * " 3", or "3.0" as a string) returns null.
 */
final class IntegerResidue
{
    /**
     * Largest float that can only come from one integer (JavaScript's
     * Number.MAX_SAFE_INTEGER). From 2^53 on, floats skip integers, so
     * 2^53 + 1 written in JSON is decoded as 2^53.
     */
    private const MAX_SAFE_FLOAT = 2 ** 53 - 1;

    public static function of(mixed $value, int $modulus): ?int
    {
        if (is_int($value)) {
            return (($value % $modulus) + $modulus) % $modulus;
        }

        if (is_float($value)) {
            if (! is_finite($value) || floor($value) !== $value || abs($value) > self::MAX_SAFE_FLOAT) {
                return null;
            }

            return self::of((int) $value, $modulus);
        }

        if (is_string($value) && preg_match('/\A(-?)(\d+)\z/', $value, $matches) === 1) {
            $residue = 0;

            foreach (str_split($matches[2]) as $digit) {
                $residue = ($residue * 10 + (int) $digit) % $modulus;
            }

            return $matches[1] === '-' ? ($modulus - $residue) % $modulus : $residue;
        }

        return null;
    }
}
