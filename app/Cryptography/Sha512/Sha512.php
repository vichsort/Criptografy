<?php

namespace App\Cryptography\Sha512;

use App\Cryptography\Contracts\AlgorithmMetadata;
use App\Cryptography\Contracts\DescribesAlgorithm;
use App\Cryptography\Contracts\HashAlgorithm;
use App\Cryptography\Contracts\OptionsSchema;

/**
 * SHA-512 via PHP's native hash(), with the same reasoning as Sha256.
 * Kept as its own module (not a parameterized variant of Sha256) because each
 * algorithm is a self-contained module; the duplication is minimal and the
 * separation is clearer than a premature abstraction.
 */
final class Sha512 implements DescribesAlgorithm, HashAlgorithm
{
    public function hash(string $input, array $options = []): string
    {
        return hash('sha512', $input);
    }

    public function describe(): AlgorithmMetadata
    {
        return new AlgorithmMetadata(
            id: 'sha-512',
            name: 'SHA-512',
            category: 'hashing',
            description: 'One-way cryptographic hash function (512 bits), using the native and trusted PHP implementation.',
            supportsEncryption: false,
            supportsDecryption: false,
            supportsHashing: true,
            requiresKey: false,
            securityLevel: 'modern',
            options: OptionsSchema::none(),
        );
    }
}
