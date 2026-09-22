<?php

namespace App\Cryptography\Vigenere;

use App\Cryptography\Contracts\InvalidAlgorithmOptionsException;

final class VigenereException extends InvalidAlgorithmOptionsException
{
    public static function missingKey(): self
    {
        return new self('O parâmetro "key" é obrigatório para o algoritmo Vigenère.');
    }

    public static function emptyKey(): self
    {
        return new self('A chave do Vigenère não pode ser vazia.');
    }

    public static function invalidKey(mixed $key): self
    {
        $value = is_scalar($key) ? (string) $key : get_debug_type($key);

        return new self(sprintf(
            'A chave do Vigenère deve conter apenas letras (A-Z). Valor recebido: "%s"',
            $value,
        ));
    }
}
