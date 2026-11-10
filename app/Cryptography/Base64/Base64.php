<?php

namespace App\Cryptography\Base64;

use App\Cryptography\Contracts\AlgorithmMetadata;
use App\Cryptography\Contracts\DescribesAlgorithm;
use App\Cryptography\Contracts\EncodingAlgorithm;
use App\Cryptography\Contracts\OptionsSchema;

/**
 * Base64 via PHP's native base64_encode()/base64_decode().
 *
 * supportsEncryption and supportsDecryption are deliberately false: this
 * module implements EncodingAlgorithm (encode/decode), not
 * EncryptionAlgorithm (encrypt/decrypt). They are different contracts and
 * HTTP endpoints (/api/encode and /api/decode, not /api/encrypt and
 * /api/decrypt). Base64 has no key and no notion of security.
 */
final class Base64 implements DescribesAlgorithm, EncodingAlgorithm
{
    public function encode(string $input): string
    {
        return base64_encode($input);
    }

    public function decode(string $input): string
    {
        // Strict mode makes base64_decode() return false on any character
        // outside the Base64 alphabet, instead of silently guessing or
        // ignoring the garbage.
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
            options: OptionsSchema::none(),
        );
    }
}
