<?php

namespace App\Cryptography\Contracts;

/**
 * Comportamento esperado de qualquer algoritmo de criptografia reversível
 * (Caesar, Vigenere, Hill, Aes256Gcm, ...).
 *
 * O contrato representa o comportamento esperado, não uma implementação
 * específica (Seção 8 da especificação).
 */
interface EncryptionAlgorithm
{
    /**
     * @param  array<string, mixed>  $options  Opções específicas do algoritmo
     *                                         (ex.: ['shift' => 3], ['key' => '...']).
     *
     * @throws InvalidAlgorithmOptionsException quando as opções são inválidas para este algoritmo.
     */
    public function encrypt(string $input, array $options = []): string;

    /**
     * @param  array<string, mixed>  $options
     *
     * @throws InvalidAlgorithmOptionsException quando as opções são inválidas para este algoritmo.
     */
    public function decrypt(string $input, array $options = []): string;
}
