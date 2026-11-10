<?php

namespace App\Cryptography\Hill;

use App\Cryptography\Contracts\AlgorithmMetadata;
use App\Cryptography\Contracts\DescribesAlgorithm;
use App\Cryptography\Contracts\EncryptionAlgorithm;
use App\Cryptography\Contracts\OptionsSchema;
use App\Cryptography\Support\IntegerResidue;

/**
 * Hill: each block of N letters is treated as a vector and transformed by an
 * N x N key matrix, in modular arithmetic (mod 26):
 *
 *     C = K × P  (mod 26)      — encrypt
 *     P = K⁻¹ × C  (mod 26)    — decrypt
 *
 * A deliberate difference from Caesar/Vigenère: since it works on fixed-size
 * BLOCKS, Hill normalizes the text to uppercase letters only before
 * processing (dropping spaces/punctuation) and pads the last block with 'X'
 * when needed. Keeping interleaved punctuation would break block alignment;
 * this is a structural limitation of classical block ciphers, not an
 * implementation oversight.
 */
final class Hill implements DescribesAlgorithm, EncryptionAlgorithm
{
    private const ALPHABET_SIZE = 26;

    private const MAX_MATRIX_SIZE = 6;

    private const PADDING_LETTER = 'X';

    public function encrypt(string $input, array $options = []): string
    {
        $matrix = $this->resolveKeyMatrix($options);
        $blockSize = count($matrix);

        $letters = $this->padToBlockSize($this->extractLetters($input), $blockSize);

        return $this->transformBlocks($letters, $matrix, $blockSize);
    }

    public function decrypt(string $input, array $options = []): string
    {
        $matrix = $this->resolveKeyMatrix($options);
        $blockSize = count($matrix);
        $inverse = $this->invertMatrixMod($matrix);

        $letters = $this->extractLetters($input);

        if ($letters !== [] && count($letters) % $blockSize !== 0) {
            throw HillException::inputLengthNotMultipleOfBlockSize($blockSize);
        }

        return $this->transformBlocks($letters, $inverse, $blockSize);
    }

    // ------------------------------------------------------------------
    // Text <-> vectors
    // ------------------------------------------------------------------

    /**
     * @return list<string> letters only, uppercase.
     */
    private function extractLetters(string $input): array
    {
        $letters = [];

        foreach (str_split($input) as $char) {
            if (ctype_alpha($char)) {
                $letters[] = strtoupper($char);
            }
        }

        return $letters;
    }

    /**
     * @param  list<string>  $letters
     * @return list<string>
     */
    private function padToBlockSize(array $letters, int $blockSize): array
    {
        while ($letters !== [] && count($letters) % $blockSize !== 0) {
            $letters[] = self::PADDING_LETTER;
        }

        return $letters;
    }

    /**
     * @param  list<string>  $letters
     * @param  list<list<int>>  $matrix
     */
    private function transformBlocks(array $letters, array $matrix, int $blockSize): string
    {
        $result = '';

        foreach (array_chunk($letters, max(1, $blockSize)) as $block) {
            $vector = array_map(
                static fn (string $letter): int => ord($letter) - ord('A'),
                $block,
            );

            $transformed = $this->multiplyMatrixVector($matrix, $vector, self::ALPHABET_SIZE);

            foreach ($transformed as $value) {
                $result .= chr($value + ord('A'));
            }
        }

        return $result;
    }

    // ------------------------------------------------------------------
    // Key validation and parsing
    // ------------------------------------------------------------------

    /**
     * @param  array<string, mixed>  $options
     * @return list<list<int>>
     *
     * @throws HillException
     */
    private function resolveKeyMatrix(array $options): array
    {
        if (! array_key_exists('key', $options)) {
            throw HillException::missingKey();
        }

        $key = $options['key'];

        if (! is_array($key) || $key === [] || ! array_is_list($key)) {
            throw HillException::invalidKeyFormat();
        }

        $size = count($key);

        if ($size < 2) {
            throw HillException::keyTooSmall();
        }

        if ($size > self::MAX_MATRIX_SIZE) {
            throw HillException::keyTooLarge(self::MAX_MATRIX_SIZE);
        }

        $matrix = [];

        foreach ($key as $row) {
            if (! is_array($row) || ! array_is_list($row) || count($row) !== $size) {
                throw HillException::invalidKeyFormat();
            }

            // Each value is reduced mod 26 as soon as it is read: the cipher
            // only depends on the matrix mod 26, and small entries keep the
            // determinant and cofactors from overflowing int.
            $matrix[] = array_map(
                static fn (mixed $value): int => IntegerResidue::of($value, self::ALPHABET_SIZE)
                    ?? throw HillException::invalidKeyFormat(),
                $row,
            );
        }

        // Hill requires the matrix to be invertible mod 26; validating here
        // makes both encrypt() and decrypt() fail early with a clear message
        // instead of producing a wrong result.
        $this->determinantInverse($matrix);

        return $matrix;
    }

    /**
     * The inverse of det(K) mod 26, which exists only when gcd(det, 26) = 1.
     *
     * @param  list<list<int>>  $matrix
     *
     * @throws HillException when the matrix is not invertible mod 26.
     */
    private function determinantInverse(array $matrix): int
    {
        $determinant = $this->mod($this->determinant($matrix), self::ALPHABET_SIZE);

        return $this->modInverse($determinant, self::ALPHABET_SIZE)
            ?? throw HillException::keyNotInvertible($determinant, self::ALPHABET_SIZE);
    }

    // ------------------------------------------------------------------
    // Matrix algebra modulo N
    // ------------------------------------------------------------------

    /**
     * @param  list<list<int>>  $matrix
     * @return list<list<int>>
     */
    private function invertMatrixMod(array $matrix): array
    {
        $determinantInverse = $this->determinantInverse($matrix);

        $adjugate = $this->transpose($this->cofactorMatrix($matrix));
        $size = count($matrix);

        $inverse = [];

        for ($i = 0; $i < $size; $i++) {
            $row = [];

            for ($j = 0; $j < $size; $j++) {
                $row[] = $this->mod($determinantInverse * $adjugate[$i][$j], self::ALPHABET_SIZE);
            }

            $inverse[] = $row;
        }

        return $inverse;
    }

    /**
     * @param  list<list<int>>  $matrix
     * @param  list<int>  $vector
     * @return list<int>
     */
    private function multiplyMatrixVector(array $matrix, array $vector, int $modulus): array
    {
        $result = [];

        foreach ($matrix as $row) {
            $sum = 0;

            foreach ($row as $column => $value) {
                $sum += $value * $vector[$column];
            }

            $result[] = $this->mod($sum, $modulus);
        }

        return $result;
    }

    /**
     * Determinant by cofactor (Laplace) expansion along the first row.
     * Recursive, which is fine for the small matrices Hill uses
     * (up to MAX_MATRIX_SIZE x MAX_MATRIX_SIZE).
     *
     * @param  list<list<int>>  $matrix
     */
    private function determinant(array $matrix): int
    {
        $size = count($matrix);

        if ($size === 1) {
            return $matrix[0][0];
        }

        if ($size === 2) {
            return $matrix[0][0] * $matrix[1][1] - $matrix[0][1] * $matrix[1][0];
        }

        $determinant = 0;

        for ($column = 0; $column < $size; $column++) {
            $sign = ($column % 2 === 0) ? 1 : -1;
            $determinant += $sign * $matrix[0][$column] * $this->determinant(
                $this->minor($matrix, 0, $column),
            );
        }

        return $determinant;
    }

    /**
     * @param  list<list<int>>  $matrix
     * @return list<list<int>>
     */
    private function cofactorMatrix(array $matrix): array
    {
        $size = count($matrix);
        $cofactors = [];

        for ($i = 0; $i < $size; $i++) {
            $row = [];

            for ($j = 0; $j < $size; $j++) {
                $sign = (($i + $j) % 2 === 0) ? 1 : -1;
                $row[] = $sign * $this->determinant($this->minor($matrix, $i, $j));
            }

            $cofactors[] = $row;
        }

        return $cofactors;
    }

    /**
     * Removes the given row and column, returning the submatrix (minor).
     *
     * @param  list<list<int>>  $matrix
     * @return list<list<int>>
     */
    private function minor(array $matrix, int $skipRow, int $skipColumn): array
    {
        $result = [];

        foreach ($matrix as $rowIndex => $row) {
            if ($rowIndex === $skipRow) {
                continue;
            }

            $newRow = [];

            foreach ($row as $columnIndex => $value) {
                if ($columnIndex === $skipColumn) {
                    continue;
                }

                $newRow[] = $value;
            }

            $result[] = $newRow;
        }

        return $result;
    }

    /**
     * @param  list<list<int>>  $matrix
     * @return list<list<int>>
     */
    private function transpose(array $matrix): array
    {
        $transposed = [];
        $size = count($matrix);

        for ($j = 0; $j < $size; $j++) {
            $transposed[] = array_column($matrix, $j);
        }

        return $transposed;
    }

    // ------------------------------------------------------------------
    // Modular arithmetic
    // ------------------------------------------------------------------

    private function mod(int $value, int $modulus): int
    {
        return (($value % $modulus) + $modulus) % $modulus;
    }

    /**
     * Multiplicative inverse of $value modulo $modulus, via the extended
     * Euclidean algorithm. Returns null when gcd($value, $modulus) != 1
     * (that is, when the inverse does not exist).
     */
    private function modInverse(int $value, int $modulus): ?int
    {
        [$gcd, $x] = $this->extendedGcd($this->mod($value, $modulus), $modulus);

        if ($gcd !== 1) {
            return null;
        }

        return $this->mod($x, $modulus);
    }

    /**
     * @return array{0: int, 1: int, 2: int} [mdc, x, y] tal que a*x + b*y = mdc(a, b)
     */
    private function extendedGcd(int $a, int $b): array
    {
        if ($a === 0) {
            return [$b, 0, 1];
        }

        [$gcd, $x1, $y1] = $this->extendedGcd($b % $a, $a);

        $x = $y1 - intdiv($b, $a) * $x1;
        $y = $x1;

        return [$gcd, $x, $y];
    }

    public function describe(): AlgorithmMetadata
    {
        return new AlgorithmMetadata(
            id: 'hill',
            name: 'Hill',
            category: 'encryption',
            description: 'Polygraphic cipher based on linear algebra: each block of letters is transformed by a key matrix in modular arithmetic (C = K x P mod 26). Operates only on letters (A-Z), normalized to uppercase.',
            supportsEncryption: true,
            supportsDecryption: true,
            supportsHashing: false,
            requiresKey: true,
            securityLevel: 'educational',
            options: OptionsSchema::object(
                properties: [
                    'key' => [
                        'type' => 'array',
                        'title' => 'Key matrix',
                        'description' => sprintf(
                            'Square matrix of integers, from 2x2 to %1$dx%1$d, whose determinant is coprime with 26 (e.g. [[3, 3], [2, 5]]).',
                            self::MAX_MATRIX_SIZE,
                        ),
                        'minItems' => 2,
                        'maxItems' => self::MAX_MATRIX_SIZE,
                        'items' => [
                            'type' => 'array',
                            'minItems' => 2,
                            'maxItems' => self::MAX_MATRIX_SIZE,
                            'items' => ['type' => 'integer'],
                        ],
                    ],
                ],
                required: ['key'],
            ),
        );
    }
}
