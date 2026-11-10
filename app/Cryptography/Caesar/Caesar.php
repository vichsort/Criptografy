<?php

namespace App\Cryptography\Caesar;

use App\Cryptography\Contracts\AlgorithmMetadata;
use App\Cryptography\Contracts\DescribesAlgorithm;
use App\Cryptography\Contracts\EncryptionAlgorithm;
use App\Cryptography\Contracts\OptionsSchema;
use App\Cryptography\Support\IntegerResidue;

/**
 * Caesar: substitution cipher that shifts the alphabet by a fixed amount.
 *
 * C = (P + shift) mod 26
 *
 * Only letters (a-z, A-Z) are transformed; every other character (spaces,
 * punctuation, digits, accented letters) is preserved unchanged. This is a
 * deliberate educational scope decision: the classical Caesar cipher works
 * on a 26-letter alphabet, not on the whole of Unicode.
 */
final class Caesar implements DescribesAlgorithm, EncryptionAlgorithm
{
    private const ALPHABET_SIZE = 26;

    public function encrypt(string $input, array $options = []): string
    {
        return $this->shiftText($input, $this->resolveShift($options));
    }

    public function decrypt(string $input, array $options = []): string
    {
        // Shifting back by k is shifting forward by 26 - k.
        return $this->shiftText($input, self::ALPHABET_SIZE - $this->resolveShift($options));
    }

    /**
     * Returns the shift already reduced to [0, 26): only its value mod 26
     * matters to the cipher, and a small non-negative shift keeps the
     * arithmetic in shiftChar() within [0, 52).
     *
     * @param  array<string, mixed>  $options
     *
     * @throws CaesarException
     */
    private function resolveShift(array $options): int
    {
        if (! array_key_exists('shift', $options)) {
            throw CaesarException::missingShift();
        }

        return IntegerResidue::of($options['shift'], self::ALPHABET_SIZE)
            ?? throw CaesarException::invalidShift($options['shift']);
    }

    private function shiftText(string $input, int $shift): string
    {
        $result = '';

        foreach (str_split($input) as $char) {
            $result .= match (true) {
                ctype_upper($char) => $this->shiftChar($char, 'A', $shift),
                ctype_lower($char) => $this->shiftChar($char, 'a', $shift),
                default => $char,
            };
        }

        return $result;
    }

    private function shiftChar(string $char, string $alphabetStart, int $shift): string
    {
        $base = ord($alphabetStart);

        return chr((ord($char) - $base + $shift) % self::ALPHABET_SIZE + $base);
    }

    public function describe(): AlgorithmMetadata
    {
        return new AlgorithmMetadata(
            id: 'caesar',
            name: 'Caesar',
            category: 'encryption',
            description: 'Substitution cipher that shifts the alphabet by a fixed amount, based on modular arithmetic (C = (P + shift) mod 26).',
            supportsEncryption: true,
            supportsDecryption: true,
            supportsHashing: false,
            requiresKey: true,
            securityLevel: 'educational',
            options: OptionsSchema::object(
                properties: [
                    'shift' => [
                        'type' => 'integer',
                        'title' => 'Shift',
                        'description' => 'Number of positions each letter is shifted. Any integer is accepted; only its value mod 26 matters.',
                    ],
                ],
                required: ['shift'],
            ),
        );
    }
}
