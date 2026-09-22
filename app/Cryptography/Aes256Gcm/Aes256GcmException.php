<?php

namespace App\Cryptography\Aes256Gcm;

use App\Cryptography\Contracts\InvalidAlgorithmOptionsException;

final class Aes256GcmException extends InvalidAlgorithmOptionsException
{
    public static function missingKey(): self
    {
        return new self('O parâmetro "key" é obrigatório para o algoritmo AES-256-GCM.');
    }

    public static function emptyKey(): self
    {
        return new self('A chave do AES-256-GCM não pode ser vazia.');
    }

    public static function invalidCiphertextFormat(): self
    {
        return new self(
            'O texto cifrado não está em um formato válido (esperado: base64 de nonce+tag+ciphertext).',
        );
    }

    public static function decryptionFailed(): self
    {
        return new self(
            'Falha ao descriptografar: chave incorreta ou dados corrompidos/adulterados '.
            '(a tag de autenticação do GCM não confere).',
        );
    }
}
