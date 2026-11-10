<?php

namespace App\Cryptography\Contracts;

use stdClass;

/**
 * Builds the JSON Schema of the options an algorithm accepts. It is exposed in
 * GET /api/algorithms so the frontend can generate and validate the form
 * without duplicating each algorithm's rules.
 *
 * Only keywords that are identical in draft-07 and 2020-12 are used, and
 * "$schema" is not declared: validators set up for draft-07 (e.g. the default
 * AJV in react-jsonschema-form) reject a schema that declares 2020-12.
 *
 * The schema is closed (additionalProperties: false): it describes exactly
 * the options the algorithm reads.
 */
final class OptionsSchema
{
    /**
     * @param  array<string, array<string, mixed>>  $properties  option name => option schema.
     * @param  list<string>  $required
     * @return array<string, mixed>
     */
    public static function object(array $properties = [], array $required = []): array
    {
        return [
            'type' => 'object',
            // An empty PHP array would serialize as [] (a list); the schema
            // requires a JSON object ({}).
            'properties' => $properties === [] ? new stdClass : $properties,
            'required' => $required,
            'additionalProperties' => false,
        ];
    }

    /**
     * Schema for algorithms that take no options (hashes, encodings).
     *
     * @return array<string, mixed>
     */
    public static function none(): array
    {
        return self::object();
    }
}
