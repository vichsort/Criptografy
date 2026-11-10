<?php

namespace App\Cryptography\Hex;

use App\Cryptography\Contracts\AlgorithmMetadata;
use App\Cryptography\Contracts\DescribesAlgorithm;
use App\Cryptography\Contracts\EncodingAlgorithm;
use App\Cryptography\Contracts\OptionsSchema;

/**
 * Hexadecimal via PHP's native bin2hex()/hex2bin().
 *
 * Like Base64, it implements EncodingAlgorithm, not EncryptionAlgorithm: no
 * key and no notion of security.
 */
final class Hex implements DescribesAlgorithm, EncodingAlgorithm
{
    public function encode(string $input): string
    {
        return bin2hex($input);
    }

    public function decode(string $input): string
    {
        if ($input === '') {
            return '';
        }

        if (! ctype_xdigit($input) || strlen($input) % 2 !== 0) {
            throw HexException::invalidInput();
        }

        // The input was validated above, so pack() (unlike hex2bin(), which
        // returns false on bad input) never needs a failure branch.
        return pack('H*', $input);
    }

    public function describe(): AlgorithmMetadata
    {
        return new AlgorithmMetadata(
            id: 'hex',
            name: 'Hexadecimal',
            category: 'encoding',
            description: 'Reversible transformation from binary data to base-16 text. It is not a security mechanism.',
            supportsEncryption: false,
            supportsDecryption: false,
            supportsHashing: false,
            requiresKey: false,
            securityLevel: 'not-applicable',
            options: OptionsSchema::none(),
        );
    }
}
