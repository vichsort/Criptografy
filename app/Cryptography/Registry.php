<?php

namespace App\Cryptography;

use App\Cryptography\Contracts\AlgorithmMetadata;
use App\Cryptography\Contracts\DescribesAlgorithm;
use LogicException;

/**
 * Associa um identificador estável (ex.: "caesar", "vigenere") à sua
 * implementação, para que o restante da aplicação nunca precise de uma
 * sequência de `if ($algorithm === 'caesar') ...` (Seção 9).
 *
 * O Registry não sabe *como* cada algoritmo funciona — apenas onde
 * encontrá-lo. Isso evita acoplamento entre controllers e implementações
 * concretas (Dependency Inversion, Seção 20).
 */
final class Registry
{
    /** @var array<string, DescribesAlgorithm> */
    private array $algorithms = [];

    /**
     * O id usado no registro é o próprio describe()->id, para que o
     * identificador exista em um único lugar e nunca divirja do que
     * GET /api/algorithms anuncia.
     *
     * @throws LogicException quando outro algoritmo já usa o mesmo id.
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
     * Metadados de todos os algoritmos registrados, para o endpoint
     * GET /api/algorithms (Seção 19) e para o frontend descobrir os
     * algoritmos disponíveis sem os codificar manualmente (Seção 16).
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
