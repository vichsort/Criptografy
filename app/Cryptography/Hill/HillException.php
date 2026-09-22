<?php

namespace App\Cryptography\Hill;

use App\Cryptography\Contracts\InvalidAlgorithmOptionsException;

final class HillException extends InvalidAlgorithmOptionsException
{
    public static function missingKey(): self
    {
        return new self('O parâmetro "key" (matriz) é obrigatório para o algoritmo Hill.');
    }

    public static function invalidKeyFormat(): self
    {
        return new self(
            'A chave do Hill deve ser uma matriz quadrada (array de arrays) de números inteiros. '.
            'Exemplo: [[3, 3], [2, 5]].',
        );
    }

    public static function keyTooSmall(): self
    {
        return new self('A matriz-chave do Hill deve ter pelo menos 2x2.');
    }

    public static function keyTooLarge(int $maxSize): self
    {
        return new self(sprintf(
            'A matriz-chave do Hill não pode ter mais que %dx%d, por limitação prática de processamento.',
            $maxSize,
            $maxSize,
        ));
    }

    public static function keyNotInvertible(int $determinant, int $modulus): self
    {
        return new self(sprintf(
            'A matriz-chave não é inversível em módulo %d: det(K) = %d e mdc(%d, %d) != 1. '.
            'Escolha uma matriz cujo determinante seja coprimo com %d.',
            $modulus,
            $determinant,
            $determinant,
            $modulus,
            $modulus,
        ));
    }

    public static function inputLengthNotMultipleOfBlockSize(int $blockSize): self
    {
        return new self(sprintf(
            'O texto cifrado deve ter um número de letras múltiplo do tamanho do bloco (%d).',
            $blockSize,
        ));
    }
}
