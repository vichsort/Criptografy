<?php

namespace App\Cryptography\Contracts;

use InvalidArgumentException;

/**
 * Classe base para exceções de opções/chaves inválidas específicas de cada
 * algoritmo (ex.: CaesarException, VigenereException — Seção 7).
 *
 * Existe para permitir que o restante da aplicação (ex.: tratamento de erros
 * HTTP em bootstrap/app.php) reaja de forma uniforme a "entrada inválida para
 * este algoritmo" sem precisar conhecer cada exceção concreta (Seção 23).
 */
abstract class InvalidAlgorithmOptionsException extends InvalidArgumentException
{
}
