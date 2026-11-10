<?php

namespace App\Cryptography\Hill;

use App\Cryptography\Contracts\AlgorithmMetadata;
use App\Cryptography\Contracts\DescribesAlgorithm;
use App\Cryptography\Contracts\EncryptionAlgorithm;
use App\Cryptography\Support\IntegerResidue;

/**
 * Hill: cada bloco de N letras é tratado como um vetor e transformado por
 * uma matriz-chave N x N, em aritmética modular (mod 26):
 *
 *     C = K × P  (mod 26)      — encrypt
 *     P = K⁻¹ × C  (mod 26)    — decrypt
 *
 * Diferença deliberada em relação a Caesar/Vigenère: por operar em BLOCOS de
 * tamanho fixo, o Hill aqui normaliza o texto para apenas letras maiúsculas
 * antes de processar (removendo espaços/pontuação) e preenche o último
 * bloco com 'X' quando necessário. Preservar pontuação intercalada
 * quebraria o alinhamento dos blocos — é uma limitação estrutural de cifras
 * de bloco clássicas, não um descuido de implementação.
 */
final class Hill implements EncryptionAlgorithm, DescribesAlgorithm
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
        $inverse = $this->invertMatrixMod($matrix, self::ALPHABET_SIZE);

        $letters = $this->extractLetters($input);

        if ($letters !== [] && count($letters) % $blockSize !== 0) {
            throw HillException::inputLengthNotMultipleOfBlockSize($blockSize);
        }

        return $this->transformBlocks($letters, $inverse, $blockSize);
    }

    // ------------------------------------------------------------------
    // Texto <-> vetores
    // ------------------------------------------------------------------

    /**
     * @return list<string> apenas letras, maiúsculas.
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

        foreach (array_chunk($letters, $blockSize) as $block) {
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
    // Validação e parsing da chave
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

            // Cada valor é reduzido mod 26 já na leitura: a cifra só depende
            // da matriz em mod 26, e entradas pequenas impedem que o
            // determinante e os cofatores estourem o limite de int.
            $matrix[] = array_map(
                static fn (mixed $value): int => IntegerResidue::of($value, self::ALPHABET_SIZE)
                    ?? throw HillException::invalidKeyFormat(),
                $row,
            );
        }

        // O Hill exige que a matriz seja inversível em mod 26; validar aqui
        // garante que tanto encrypt() quanto decrypt() falhem cedo e com uma
        // mensagem clara, em vez de produzir um resultado incorreto.
        $determinant = $this->mod($this->determinant($matrix), self::ALPHABET_SIZE);

        if ($this->modInverse($determinant, self::ALPHABET_SIZE) === null) {
            throw HillException::keyNotInvertible($determinant, self::ALPHABET_SIZE);
        }

        return $matrix;
    }

    // ------------------------------------------------------------------
    // Álgebra matricial em módulo N
    // ------------------------------------------------------------------

    /**
     * @param  list<list<int>>  $matrix
     * @return list<list<int>>
     */
    private function invertMatrixMod(array $matrix, int $modulus): array
    {
        $determinant = $this->mod($this->determinant($matrix), $modulus);
        $determinantInverse = $this->modInverse($determinant, $modulus);

        if ($determinantInverse === null) {
            throw HillException::keyNotInvertible($determinant, $modulus);
        }

        $adjugate = $this->transpose($this->cofactorMatrix($matrix));
        $size = count($matrix);

        $inverse = [];

        for ($i = 0; $i < $size; $i++) {
            $inverse[$i] = [];

            for ($j = 0; $j < $size; $j++) {
                $inverse[$i][$j] = $this->mod($determinantInverse * $adjugate[$i][$j], $modulus);
            }
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
     * Determinante por expansão de cofatores (Laplace) na primeira linha.
     * Recursivo, adequado para as matrizes pequenas usadas no Hill
     * (até MAX_MATRIX_SIZE x MAX_MATRIX_SIZE).
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
            $cofactors[$i] = [];

            for ($j = 0; $j < $size; $j++) {
                $sign = (($i + $j) % 2 === 0) ? 1 : -1;
                $cofactors[$i][$j] = $sign * $this->determinant($this->minor($matrix, $i, $j));
            }
        }

        return $cofactors;
    }

    /**
     * Remove a linha e a coluna indicadas, retornando a submatriz (menor).
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

        foreach ($matrix as $i => $row) {
            foreach ($row as $j => $value) {
                $transposed[$j][$i] = $value;
            }
        }

        return $transposed;
    }

    // ------------------------------------------------------------------
    // Aritmética modular
    // ------------------------------------------------------------------

    private function mod(int $value, int $modulus): int
    {
        return (($value % $modulus) + $modulus) % $modulus;
    }

    /**
     * Inverso multiplicativo de $value em módulo $modulus, via algoritmo de
     * Euclides estendido. Retorna null quando mdc($value, $modulus) != 1
     * (ou seja, quando o inverso não existe).
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
            description: 'Cifra poligráfica baseada em álgebra linear: cada bloco de letras é transformado por uma matriz-chave em aritmética modular (C = K × P mod 26). Opera apenas sobre letras (A-Z), normalizadas em maiúsculas.',
            supportsEncryption: true,
            supportsDecryption: true,
            supportsHashing: false,
            requiresKey: true,
            securityLevel: 'educational',
        );
    }
}
