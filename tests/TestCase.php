<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * The algorithm catalog as GET /api/algorithms returns it, one decoded
     * metadata array per algorithm.
     *
     * @return list<array<mixed>>
     */
    protected function listedAlgorithms(): array
    {
        $data = $this->getJson('/api/algorithms')->assertOk()->json('data');
        $this->assertIsList($data);

        return array_map(
            fn (mixed $algorithm): array => is_array($algorithm)
                ? $algorithm
                : $this->fail('Each catalog entry must be a JSON object.'),
            $data,
        );
    }
}
