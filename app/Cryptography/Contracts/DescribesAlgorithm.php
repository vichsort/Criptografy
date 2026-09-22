<?php

namespace App\Cryptography\Contracts;

/**
 * Permite que o frontend descubra os algoritmos disponíveis e seus metadados
 * sem precisar codificar manualmente toda a lista (Seção 16).
 */
interface DescribesAlgorithm
{
    public function describe(): AlgorithmMetadata;
}
