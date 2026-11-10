<?php

namespace App\Cryptography\Sha256;

use App\Cryptography\Contracts\AlgorithmMetadata;
use App\Cryptography\Contracts\DescribesAlgorithm;
use App\Cryptography\Contracts\HashAlgorithm;

/**
 * SHA-256 via hash() nativo do PHP. Sem implementação manual — reproduzir
 * uma função de hash existente não agregaria nada ao produto (Seção 13).
 *
 * Hash != Encryption: não implementa EncryptionAlgorithm, não possui
 * decrypt() (Interface Segregation, Seção 20).
 */
final class Sha256 implements HashAlgorithm, DescribesAlgorithm
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
        );
    }
}
