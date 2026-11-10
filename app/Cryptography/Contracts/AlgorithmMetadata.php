<?php

namespace App\Cryptography\Contracts;

use JsonSerializable;

/**
 * Metadata an algorithm exposes about itself, so the frontend can render the
 * controls it needs without duplicating each algorithm's rules.
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
        /**
         * JSON Schema of the accepted options (see OptionsSchema).
         *
         * @var array<string, mixed>
         */
        public array $options,
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
     *     options: array<string, mixed>,
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
            'options' => $this->options,
        ];
    }
}
