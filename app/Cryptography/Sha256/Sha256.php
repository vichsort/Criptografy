<?php

namespace App\Cryptography\Sha256;

use App\Cryptography\Contracts\AlgorithmMetadata;
use App\Cryptography\Contracts\DescribesAlgorithm;
use App\Cryptography\Contracts\HashAlgorithm;
use App\Cryptography\Contracts\OptionsSchema;

/**
 * SHA-256 via PHP's native hash(). No manual implementation: reproducing an
 * existing hash function would add nothing.
 *
 * Hashing is not encryption: it does not implement EncryptionAlgorithm and
 * has no decrypt() (Interface Segregation).
 */
final class Sha256 implements DescribesAlgorithm, HashAlgorithm
{
    public function hash(string $input, array $options = []): string
    {
        return hash('sha256', $input);
    }

    public function describe(): AlgorithmMetadata
    {
        return new AlgorithmMetadata(
            id: 'sha-256',
            name: 'SHA-256',
            category: 'hashing',
            description: 'One-way cryptographic hash function (256 bits), using the native and trusted PHP implementation.',
            supportsEncryption: false,
            supportsDecryption: false,
            supportsHashing: true,
            requiresKey: false,
            securityLevel: 'modern',
            options: OptionsSchema::none(),
        );
    }
}
