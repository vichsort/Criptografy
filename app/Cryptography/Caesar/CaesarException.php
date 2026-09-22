<?php

namespace App\Cryptography\Caesar;

use App\Cryptography\Contracts\InvalidAlgorithmOptionsException;

final class CaesarException extends InvalidAlgorithmOptionsException
{
    public static function missingShift(): self
    {
        return new self('O parâmetro "shift" é obrigatório para o algoritmo Caesar.');
    }

    public static function invalidShift(mixed $shift): self
    {
        $value = is_scalar($shift) ? (string) $shift : get_debug_type($shift);

        return new self(sprintf(
            'O parâmetro "shift" deve ser um número inteiro. Valor recebido: %s',
            $value,
        ));
    }
}
