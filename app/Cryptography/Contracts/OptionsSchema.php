<?php

namespace App\Cryptography\Contracts;

use stdClass;

/**
 * Monta o JSON Schema das opções aceitas por um algoritmo, exposto em
 * GET /api/algorithms para que o frontend gere e valide o formulário sem
 * duplicar as regras de cada algoritmo.
 *
 * Usa apenas palavras-chave idênticas no draft-07 e no 2020-12, e não declara
 * "$schema": validadores configurados para draft-07 (ex.: o AJV padrão do
 * react-jsonschema-form) recusam um schema que declare 2020-12.
 *
 * O schema é fechado (additionalProperties: false): descreve exatamente as
 * opções que o algoritmo lê.
 */
final class OptionsSchema
{
    /**
     * @param  array<string, array<string, mixed>>  $properties  nome da opção => schema da opção.
     * @param  list<string>  $required
     * @return array<string, mixed>
     */
    public static function object(array $properties = [], array $required = []): array
    {
        return [
            'type' => 'object',
            // Um array PHP vazio seria serializado como [] (lista); o schema
            // exige um objeto JSON ({}).
            'properties' => $properties === [] ? new stdClass() : $properties,
            'required' => $required,
            'additionalProperties' => false,
        ];
    }

    /**
     * Schema de quem não aceita opções (hashes, encodings).
     *
     * @return array<string, mixed>
     */
    public static function none(): array
    {
        return self::object();
    }
}
