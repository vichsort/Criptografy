<?php

namespace App\Cryptography\Vigenere;

use App\Cryptography\Contracts\AlgorithmMetadata;
use App\Cryptography\Contracts\DescribesAlgorithm;
use App\Cryptography\Contracts\EncryptionAlgorithm;

/**
 * Vigenère: soma modular entre o texto e uma chave repetida ao longo dele.
 *
 * C[i] = (P[i] + K[i mod len(K)]) mod 26
 *
 * Assim como no Caesar, apenas letras (a-z, A-Z) são transformadas; outros
 * caracteres são preservados e — decisão relevante para a correção do
 * algoritmo — NÃO avançam a posição da chave, para que a mesma frase com ou
 * sem pontuação/espaços produza o mesmo deslocamento letra a letra.
 */
final class Vigenere implements EncryptionAlgorithm, DescribesAlgorithm
{
    private const ALPHABET_SIZE = 26;

    public function encrypt(string $input, array $options = []): string
    {
        return $this->apply($input, $this->resolveKey($options), direction: 1);
    }

    public function decrypt(string $input, array $options = []): string
    {
        return $this->apply($input, $this->resolveKey($options), direction: -1);
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

    /**
     * @param  int  $direction  1 para encrypt, -1 para decrypt
     */
    private function apply(string $input, string $key, int $direction): string
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
            $shift = $direction * $keyShift;

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
        $shift = (($shift % self::ALPHABET_SIZE) + self::ALPHABET_SIZE) % self::ALPHABET_SIZE;

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
        );
    }
}
