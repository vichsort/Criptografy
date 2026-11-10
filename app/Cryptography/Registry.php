<?php

namespace App\Cryptography;

use App\Cryptography\Contracts\AlgorithmMetadata;
use App\Cryptography\Contracts\DescribesAlgorithm;
use LogicException;

/**
 * Maps a stable identifier (e.g. "caesar", "vigenere") to its implementation,
 * so the rest of the application never needs a chain of
 * `if ($algorithm === 'caesar') ...`.
 *
 * The Registry does not know *how* each algorithm works, only where to find
 * it. This keeps controllers decoupled from concrete implementations
 * (Dependency Inversion).
 */
final class Registry
{
    /** @var array<string, DescribesAlgorithm> */
    private array $algorithms = [];

    /**
     * The algorithm is registered under its own describe()->id, so the
     * identifier lives in a single place and never diverges from what
     * GET /api/algorithms announces.
     *
     * @throws LogicException when another algorithm already uses the same id.
     */
    public function register(DescribesAlgorithm $algorithm): void
    {
        $id = $algorithm->describe()->id;

        if (isset($this->algorithms[$id])) {
            throw new LogicException(sprintf('Algorithm id "%s" is already registered.', $id));
        }

        $this->algorithms[$id] = $algorithm;
    }

    public function has(string $id): bool
    {
        return isset($this->algorithms[$id]);
    }

    /**
     * @throws AlgorithmNotFoundException
     */
    public function get(string $id): DescribesAlgorithm
    {
        return $this->algorithms[$id] ?? throw new AlgorithmNotFoundException($id);
    }

    /**
     * Metadata of every registered algorithm, served by GET /api/algorithms
     * so the frontend can discover the available algorithms without
     * hardcoding them.
     *
     * @return list<AlgorithmMetadata>
     */
    public function all(): array
    {
        return array_values(array_map(
            static fn (DescribesAlgorithm $algorithm): AlgorithmMetadata => $algorithm->describe(),
            $this->algorithms,
        ));
    }
}
