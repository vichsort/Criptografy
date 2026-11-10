<?php

namespace App\Cryptography\Caesar;

use App\Cryptography\Contracts\AlgorithmMetadata;
use App\Cryptography\Contracts\DescribesAlgorithm;
use App\Cryptography\Contracts\EncryptionAlgorithm;
use App\Cryptography\Support\IntegerResidue;

/**
 * Caesar: cifra de substituição por deslocamento fixo do alfabeto.
 *
 * C = (P + shift) mod 26
 *
 * Apenas letras (a-z, A-Z) são transformadas; qualquer outro caractere
 * (espaços, pontuação, dígitos, acentos) é preservado sem alteração.
 * Isso é uma decisão deliberada de escopo educacional: o Caesar clássico
 * opera sobre um alfabeto de 26 letras, não sobre Unicode completo.
 */
final class Caesar implements EncryptionAlgorithm, DescribesAlgorithm
{
    private const ALPHABET_SIZE = 26;

    public function encrypt(string $input, array $options = []): string
    {
        return $this->shiftText($input, $this->resolveShift($options));
    }

    public function decrypt(string $input, array $options = []): string
    {
        return $this->shiftText($input, -$this->resolveShift($options));
    }

    /**
     * Retorna o deslocamento já reduzido a [0, 26). Só o valor mod 26 importa
     * para a cifra, e reduzir aqui evita overflow ao negar o shift no decrypt.
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
        // Normaliza para o intervalo [0, 26) antes de aplicar, para que
        // deslocamentos negativos (decrypt) e maiores que 26 funcionem
        // corretamente com a aritmética modular do PHP.
        $shift = (($shift % self::ALPHABET_SIZE) + self::ALPHABET_SIZE) % self::ALPHABET_SIZE;

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
            description: 'Cifra de substituição por deslocamento fixo do alfabeto, baseada em aritmética modular (C = (P + shift) mod 26).',
            supportsEncryption: true,
            supportsDecryption: true,
            supportsHashing: false,
            requiresKey: true,
            securityLevel: 'educational',
        );
    }
}
