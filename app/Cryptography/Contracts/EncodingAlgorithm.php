<?php

namespace App\Cryptography\Contracts;

/**
 * Comportamento esperado de uma transformação de encoding (Base64, Hex, ...).
 *
 * Diferente de EncryptionAlgorithm: encode/decode não possuem noção de chave
 * nem de segurança — são apenas mudanças de representação (Seção 14).
 */
interface EncodingAlgorithm
{
    public function encode(string $input): string;

    /**
     * @throws InvalidAlgorithmOptionsException quando o input não está no formato esperado.
     */
    public function decode(string $input): string;
}
