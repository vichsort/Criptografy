<?php

namespace Tests\Feature\Cryptography;

use Tests\TestCase;

final class AlgorithmControllerTest extends TestCase
{
    public function test_lists_registered_algorithms_with_their_metadata(): void
    {
        $response = $this->getJson('/api/algorithms');

        $response->assertOk();
        $response->assertJsonFragment(['id' => 'caesar']);
        $response->assertJsonPath('data.0.supportsHashing', false);
    }

    public function test_exposes_the_options_of_each_algorithm_as_json_schema(): void
    {
        $caesar = collect($this->getJson('/api/algorithms')->json('data'))->firstWhere('id', 'caesar');

        $this->assertSame('object', $caesar['options']['type']);
        $this->assertSame(['shift'], $caesar['options']['required']);
        $this->assertSame('integer', $caesar['options']['properties']['shift']['type']);
        $this->assertFalse($caesar['options']['additionalProperties']);
    }

    public function test_algorithms_without_options_expose_an_empty_object_schema(): void
    {
        // "properties" precisa ser um objeto JSON ({}), não um array ([]):
        // validadores de JSON Schema recusam o segundo.
        $algorithms = json_decode($this->getJson('/api/algorithms')->getContent())->data;
        $sha256 = collect($algorithms)->firstWhere('id', 'sha-256');

        $this->assertEquals(new \stdClass, $sha256->options->properties);
        $this->assertSame([], $sha256->options->required);
        $this->assertFalse($sha256->options->additionalProperties);
    }
}
