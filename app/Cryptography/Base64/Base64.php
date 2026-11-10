<?php

namespace App\Cryptography\Base64;

use App\Cryptography\Contracts\AlgorithmMetadata;
use App\Cryptography\Contracts\DescribesAlgorithm;
use App\Cryptography\Contracts\EncodingAlgorithm;

/**
 * Base64 via base64_encode()/base64_decode() nativos do PHP.
 *
 * supportsEncryption e supportsDecryption ficam false propositalmente: este
 * módulo implementa EncodingAlgorithm (encode/decode), não
 * EncryptionAlgorithm (encrypt/decrypt) — são contratos e endpoints HTTP
 * diferentes (/api/encode e /api/decode, não /api/encrypt e /api/decrypt).
 * Base64 não possui chave nem noção de segurança (Seção 14).
 */
final class Base64 implements EncodingAlgorithm, DescribesAlgorithm
{
    public function encode(string $input): string
    {
        return base64_encode($input);
    }

    public function decode(string $input): string
    {
        // O modo estrito faz base64_decode() retornar false diante de
        // qualquer caractere fora do alfabeto Base64, em vez de tentar
        // adivinhar/ignorar o lixo silenciosamente.
        $decoded = base64_decode($input, true);

        if ($decoded === false) {
            throw Base64Exception::invalidInput();
        }

        return $decoded;
    }

    public function describe(): AlgorithmMetadata
    {
        return new AlgorithmMetadata(
            id: 'base64',
            name: 'Base64',
            category: 'encoding',
            description: 'Reversible transformation from binary data to ASCII text. It is not a security mechanism.',
            supportsEncryption: false,
            supportsDecryption: false,
            supportsHashing: false,
            requiresKey: false,
            securityLevel: 'not-applicable',
        );
    }
}
