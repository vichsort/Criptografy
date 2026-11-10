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
        yield 'positive int' => [29, 3];
        yield 'negative int' => [-1, 25];
        yield 'zero' => [0, 0];
        yield 'PHP_INT_MAX' => [PHP_INT_MAX, 7];
        yield 'PHP_INT_MIN' => [PHP_INT_MIN, 18];
        yield 'integral float' => [3.0, 3];
        yield 'largest safe float' => [9007199254740991.0, 5];
        yield 'smallest safe float' => [-9007199254740991.0, 21];
        yield 'positive string' => ['29', 3];
        yield 'negative string' => ['-1', 25];
        yield 'string with leading zero' => ['03', 3];
        yield 'string minus zero' => ['-0', 0];
        yield 'string beyond 64-bit int' => ['99999999999999999999', 21];
        yield 'negative string beyond int' => ['-99999999999999999999', 5];
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
        yield 'double sign' => ['--3'];
        yield 'trailing sign' => ['3-'];
        yield 'plus sign' => ['+3'];
        yield 'sign only' => ['-'];
        yield 'empty string' => [''];
        yield 'surrounding spaces' => [' 3 '];
        yield 'decimal string' => ['3.0'];
        yield 'fractional float' => [3.5];
        // 2^53 is where floats stop representing every integer: the JSON
        // number 9007199254740993.0 is decoded as 2^53, so 2^53 is ambiguous.
        yield 'float at 2^53' => [9007199254740992.0];
        yield 'float at -2^53' => [-9007199254740992.0];
        yield 'float beyond exact precision' => [1e20];
        yield 'infinity' => [INF];
        yield 'NaN' => [NAN];
        yield 'boolean' => [true];
        yield 'null' => [null];
        yield 'array' => [[3]];
    }

    #[DataProvider('nonIntegerValues')]
    public function test_returns_null_for_values_that_are_not_integers(mixed $value): void
    {
        $this->assertNull(IntegerResidue::of($value, 26));
    }
}
