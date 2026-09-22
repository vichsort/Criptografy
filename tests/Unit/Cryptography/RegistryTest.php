<?php

namespace Tests\Unit\Cryptography;

use App\Cryptography\AlgorithmNotFoundException;
use App\Cryptography\Contracts\AlgorithmMetadata;
use App\Cryptography\Contracts\DescribesAlgorithm;
use App\Cryptography\Registry;
use LogicException;
use PHPUnit\Framework\TestCase;

final class RegistryTest extends TestCase
{
    public function test_has_returns_false_for_unregistered_algorithm(): void
    {
        $registry = new Registry();

        $this->assertFalse($registry->has('vigenere'));
    }

    public function test_register_and_get_returns_the_same_instance(): void
    {
        $registry = new Registry();
        $algorithm = $this->fakeAlgorithm('caesar');

        $registry->register('caesar', $algorithm);

        $this->assertTrue($registry->has('caesar'));
        $this->assertSame($algorithm, $registry->get('caesar'));
    }

    public function test_get_throws_for_unregistered_algorithm(): void
    {
        $registry = new Registry();

        $this->expectException(AlgorithmNotFoundException::class);

        $registry->get('does-not-exist');
    }

    public function test_all_aggregates_metadata_of_every_registered_algorithm(): void
    {
        $registry = new Registry();
        $registry->register('caesar', $this->fakeAlgorithm('caesar'));
        $registry->register('vigenere', $this->fakeAlgorithm('vigenere'));

        $ids = array_map(
            fn (AlgorithmMetadata $metadata) => $metadata->id,
            $registry->all(),
        );

        $this->assertCount(2, $ids);
        $this->assertContains('caesar', $ids);
        $this->assertContains('vigenere', $ids);
    }

    public function test_all_rejects_algorithms_that_do_not_describe_themselves(): void
    {
        $registry = new Registry();
        $registry->register('broken', new class {});

        $this->expectException(LogicException::class);

        $registry->all();
    }

    private function fakeAlgorithm(string $id): DescribesAlgorithm
    {
        return new class($id) implements DescribesAlgorithm
        {
            public function __construct(private readonly string $id) {}

            public function describe(): AlgorithmMetadata
            {
                return new AlgorithmMetadata(
                    id: $this->id,
                    name: $this->id,
                    category: 'encryption',
                    description: 'fake',
                    supportsEncryption: true,
                    supportsDecryption: true,
                    supportsHashing: false,
                    requiresKey: true,
                    securityLevel: 'educational',
                );
            }
        };
    }
}
