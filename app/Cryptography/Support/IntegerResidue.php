<?php

namespace App\Cryptography\Support;

/**
 * Interpreta um valor vindo do cliente como inteiro e devolve seu resto em
 * módulo N, no intervalo [0, N).
 *
 * Cifras clássicas (Caesar, Hill) só dependem dos parâmetros em mod 26.
 * Reduzir já na leitura evita dois problemas:
 *
 *  - overflow: aritmética com valores próximos de PHP_INT_MAX/MIN vira float
 *    e quebra as funções tipadas como int;
 *  - perda silenciosa de valor: `(int) "99999999999999999999"` satura em
 *    PHP_INT_MAX. Aqui strings de qualquer tamanho são reduzidas dígito a
 *    dígito, sem nunca materializar o número inteiro.
 *
 * Aceita: int; float com valor inteiro exato (|v| <= 2^53, ex.: JSON "3.0");
 * string no formato /^-?\d+$/. Qualquer outra coisa (inclusive "--3", "+3",
 * " 3", "3.0" em string) retorna null.
 */
final class IntegerResidue
{
    /** Maior inteiro que um float IEEE 754 de 64 bits representa sem perda. */
    private const MAX_EXACT_FLOAT = 2 ** 53;

    public static function of(mixed $value, int $modulus): ?int
    {
        if (is_int($value)) {
            return (($value % $modulus) + $modulus) % $modulus;
        }

        if (is_float($value)) {
            if (! is_finite($value) || floor($value) !== $value || abs($value) > self::MAX_EXACT_FLOAT) {
                return null;
            }

            return self::of((int) $value, $modulus);
        }

        if (is_string($value) && preg_match('/\A(-?)(\d+)\z/', $value, $matches) === 1) {
            $residue = 0;

            foreach (str_split($matches[2]) as $digit) {
                $residue = ($residue * 10 + (int) $digit) % $modulus;
            }

            return $matches[1] === '-' ? ($modulus - $residue) % $modulus : $residue;
        }

        return null;
    }
}
