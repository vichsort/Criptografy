<?php

namespace App\Cryptography\Contracts;

/**
 * Comportamento esperado de um algoritmo de hashing (SHA-256, SHA-512, ...).
 *
 * Propositalmente não possui método de "descriptografar" — Hash != Encryption
 * (Seção 13 da especificação). Interface Segregation (Seção 20): um hash não
 * é obrigado a implementar EncryptionAlgorithm.
 */
interface HashAlgorithm
{
    /**
     * @param  array<string, mixed>  $options
     */
    public function hash(string $input, array $options = []): string;
}
