<?php

namespace App\Cryptography\Contracts;

/**
 * Lets the frontend discover the available algorithms and their metadata
 * without hardcoding the list.
 */
interface DescribesAlgorithm
{
    public function describe(): AlgorithmMetadata;
}
