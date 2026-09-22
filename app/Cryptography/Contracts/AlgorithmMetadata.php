<?php

namespace App\Cryptography\Contracts;

use JsonSerializable;

/**
 * Metadados que um algoritmo expõe sobre si mesmo, para que o frontend
 * possa renderizar os controles necessários sem duplicar as regras de
 * cada algoritmo (Seções 15 e 16).
 */
final readonly class AlgorithmMetadata implements JsonSerializable
{
    public function __construct(
        public string $id,
        public string $name,
        public string $category,
        public string $description,
        public bool $supportsEncryption,
        public bool $supportsDecryption,
        public bool $supportsHashing,
        public bool $requiresKey,
        public string $securityLevel,
    ) {}

    /**
     * @return array{
     *     id: string,
     *     name: string,
     *     category: string,
     *     description: string,
     *     supportsEncryption: bool,
     *     supportsDecryption: bool,
     *     supportsHashing: bool,
     *     requiresKey: bool,
     *     securityLevel: string,
     * }
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'category' => $this->category,
            'description' => $this->description,
            'supportsEncryption' => $this->supportsEncryption,
            'supportsDecryption' => $this->supportsDecryption,
            'supportsHashing' => $this->supportsHashing,
            'requiresKey' => $this->requiresKey,
            'securityLevel' => $this->securityLevel,
        ];
    }
}
