<?php

namespace Tests\Unit\Cryptography\Support;

use App\Cryptography\Support\IntegerResidue;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class IntegerResidueTest extends TestCase
{
    /**
     * @return iterable<string, array{mixed, int}>
     */
    public static function integerLikeValues(): iterable
    {
        yield 'int positivo' => [29, 3];
        yield 'int negativo' => [-1, 25];
        yield 'zero' => [0, 0];
        yield 'PHP_INT_MAX' => [PHP_INT_MAX, 7];
        yield 'PHP_INT_MIN' => [PHP_INT_MIN, 18];
        yield 'float inteiro' => [3.0, 3];
        yield 'string positiva' => ['29', 3];
        yield 'string negativa' => ['-1', 25];
        yield 'string com zero à esquerda' => ['03', 3];
        yield 'string menos zero' => ['-0', 0];
        yield 'string além do int de 64 bits' => ['99999999999999999999', 21];
        yield 'string negativa além do int' => ['-99999999999999999999', 5];
    }

    #[DataProvider('integerLikeValues')]
    public function test_returns_the_residue_modulo_26(mixed $value, int $expected): void
    {
        $this->assertSame($expected, IntegerResidue::of($value, 26));
    }

    /**
     * @return iterable<string, array{mixed}>
     */
    public static function nonIntegerValues(): iterable
    {
        yield 'sinal duplicado' => ['--3'];
        yield 'sinal no fim' => ['3-'];
        yield 'sinal de mais' => ['+3'];
        yield 'só o sinal' => ['-'];
        yield 'string vazia' => [''];
        yield 'espaços' => [' 3 '];
        yield 'decimal em string' => ['3.0'];
        yield 'float fracionário' => [3.5];
        yield 'float além da precisão exata' => [1e20];
        yield 'infinito' => [INF];
        yield 'NaN' => [NAN];
        yield 'booleano' => [true];
        yield 'null' => [null];
        yield 'array' => [[3]];
    }

    #[DataProvider('nonIntegerValues')]
    public function test_returns_null_for_values_that_are_not_integers(mixed $value): void
    {
        $this->assertNull(IntegerResidue::of($value, 26));
    }
}
