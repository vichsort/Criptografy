<?php

namespace App\Cryptography\Sha512;

use App\Cryptography\Contracts\AlgorithmMetadata;
use App\Cryptography\Contracts\DescribesAlgorithm;
use App\Cryptography\Contracts\HashAlgorithm;
use App\Cryptography\Contracts\OptionsSchema;

/**
 * SHA-512 via hash() nativo do PHP — mesmo raciocínio do Sha256 (Seção 13).
 * Mantido como módulo próprio (não uma variação parametrizada de Sha256)
 * porque cada algoritmo é um módulo autocontido (Seção 7); a duplicação
 * aqui é mínima e a separação é mais clara que uma abstração prematura
 * (Seção 21).
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
