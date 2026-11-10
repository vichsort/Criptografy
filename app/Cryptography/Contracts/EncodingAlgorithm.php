<?php

namespace App\Cryptography\Contracts;

/**
 * Expected behavior of an encoding transformation (Base64, Hex, ...).
 *
 * Unlike EncryptionAlgorithm, encode/decode have no notion of a key or of
 * security: they only change the representation of the data.
 */
interface EncodingAlgorithm
{
    public function encode(string $input): string;

    /**
     * @throws InvalidAlgorithmOptionsException when the input is not in the expected format.
     */
    public function decode(string $input): string;
}
