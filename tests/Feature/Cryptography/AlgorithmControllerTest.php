<?php

namespace Tests\Feature\Cryptography;

use Illuminate\Support\Arr;
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
        $caesar = Arr::first($this->listedAlgorithms(), fn (array $algorithm): bool => $algorithm['id'] === 'caesar');

        $this->assertSame('object', data_get($caesar, 'options.type'));
        $this->assertSame(['shift'], data_get($caesar, 'options.required'));
        $this->assertSame('integer', data_get($caesar, 'options.properties.shift.type'));
        $this->assertFalse(data_get($caesar, 'options.additionalProperties'));
    }

    public function test_algorithms_without_options_expose_an_empty_object_schema(): void
    {
        // "properties" must be a JSON object ({}), not an array ([]):
        // JSON Schema validators reject the latter.
        $content = $this->getJson('/api/algorithms')->getContent();
        $this->assertIsString($content);

        $algorithms = data_get(json_decode($content, flags: JSON_THROW_ON_ERROR), 'data');
        $this->assertIsArray($algorithms);

        $sha256 = Arr::first($algorithms, fn (mixed $algorithm): bool => data_get($algorithm, 'id') === 'sha-256');

        $this->assertEquals(new \stdClass, data_get($sha256, 'options.properties'));
        $this->assertSame([], data_get($sha256, 'options.required'));
        $this->assertFalse(data_get($sha256, 'options.additionalProperties'));
    }
}
