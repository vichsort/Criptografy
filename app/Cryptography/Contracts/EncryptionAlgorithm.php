<?php

namespace App\Cryptography\Contracts;

/**
 * Expected behavior of any reversible encryption algorithm
 * (Caesar, Vigenere, Hill, Aes256Gcm, ...).
 *
 * The contract describes behavior, not a specific implementation.
 */
interface EncryptionAlgorithm
{
    /**
     * @param  array<string, mixed>  $options  Algorithm-specific options
     *                                         (e.g. ['shift' => 3], ['key' => '...']).
     *
     * @throws InvalidAlgorithmOptionsException when the options are invalid for this algorithm.
     */
    public function encrypt(string $input, array $options = []): string;

    /**
     * @param  array<string, mixed>  $options
     *
     * @throws InvalidAlgorithmOptionsException when the options are invalid for this algorithm.
     */
    public function decrypt(string $input, array $options = []): string;
}
