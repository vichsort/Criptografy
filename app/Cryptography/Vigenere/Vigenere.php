<?php

namespace App\Cryptography\Vigenere;

use App\Cryptography\Contracts\AlgorithmMetadata;
use App\Cryptography\Contracts\DescribesAlgorithm;
use App\Cryptography\Contracts\EncryptionAlgorithm;
use App\Cryptography\Contracts\OptionsSchema;

/**
 * Vigenère: modular sum of the text and a key repeated along it.
 *
 * C[i] = (P[i] + K[i mod len(K)]) mod 26
 *
 * As in Caesar, only letters (a-z, A-Z) are transformed; other characters are
 * preserved and, importantly for correctness, do NOT advance the key
 * position, so the same sentence with or without punctuation/spaces gets the
 * same shift letter by letter.
 */
final class Vigenere implements DescribesAlgorithm, EncryptionAlgorithm
{
    private const ALPHABET_SIZE = 26;

    public function encrypt(string $input, array $options = []): string
    {
        return $this->apply($input, $this->resolveKey($options), decrypt: false);
    }

    public function decrypt(string $input, array $options = []): string
    {
        return $this->apply($input, $this->resolveKey($options), decrypt: true);
    }

    /**
     * @param  array<string, mixed>  $options
     *
     * @throws VigenereException
     */
    private function resolveKey(array $options): string
    {
        if (! array_key_exists('key', $options)) {
            throw VigenereException::missingKey();
        }

        $key = $options['key'];

        if (! is_string($key)) {
            throw VigenereException::invalidKey($key);
        }

        if ($key === '') {
            throw VigenereException::emptyKey();
        }

        if (! ctype_alpha($key)) {
            throw VigenereException::invalidKey($key);
        }

        return strtoupper($key);
    }

    private function apply(string $input, string $key, bool $decrypt): string
    {
        $keyLength = strlen($key);
        $keyIndex = 0;
        $result = '';

        foreach (str_split($input) as $char) {
            if (! ctype_alpha($char)) {
                $result .= $char;

                continue;
            }

            $keyShift = ord($key[$keyIndex % $keyLength]) - ord('A');
            // Shifting back by k is shifting forward by 26 - k, which keeps
            // the shift non-negative for PHP's % operator.
            $shift = $decrypt ? self::ALPHABET_SIZE - $keyShift : $keyShift;

            $result .= match (true) {
                ctype_upper($char) => $this->shiftChar($char, 'A', $shift),
                default => $this->shiftChar($char, 'a', $shift),
            };

            $keyIndex++;
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
            id: 'vigenere',
            name: 'Vigenère',
            category: 'encryption',
            description: 'Polyalphabetic cipher: modular sum of the text and a repeating key (C = (P + K) mod 26).',
            supportsEncryption: true,
            supportsDecryption: true,
            supportsHashing: false,
            requiresKey: true,
            securityLevel: 'educational',
            options: OptionsSchema::object(
                properties: [
                    'key' => [
                        'type' => 'string',
                        'title' => 'Key',
                        'description' => 'Keyword made only of letters (A-Z, case-insensitive), repeated along the text.',
                        // "+" already rules out the empty string.
                        'pattern' => '^[A-Za-z]+$',
                    ],
                ],
                required: ['key'],
            ),
        );
    }
}
