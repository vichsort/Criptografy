<?php

namespace App\Cryptography\Contracts;

/**
 * Expected behavior of a hashing algorithm (SHA-256, SHA-512, ...).
 *
 * Deliberately has no "decrypt" method: hashing is not encryption. Following
 * Interface Segregation, a hash is never forced to implement
 * EncryptionAlgorithm.
 */
interface HashAlgorithm
{
    /**
     * @param  array<string, mixed>  $options
     */
    public function hash(string $input, array $options = []): string;
}
