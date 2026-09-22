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
}
