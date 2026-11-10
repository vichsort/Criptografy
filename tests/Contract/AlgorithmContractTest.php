<?php

namespace Tests\Contract;

use App\Cryptography\Contracts\DescribesAlgorithm;
use App\Cryptography\Contracts\EncodingAlgorithm;
use App\Cryptography\Contracts\EncryptionAlgorithm;
use App\Cryptography\Contracts\HashAlgorithm;
use App\Cryptography\Contracts\InvalidAlgorithmOptionsException;
use App\Cryptography\Registry;
use Tests\TestCase;

/**
 * Testes de contrato (Seção 22): verificam, para TODO algoritmo registrado
 * no Registry real, o comportamento que cada interface promete. Um algoritmo
 * novo é coberto automaticamente ao ser registrado no
 * CryptographyServiceProvider; algoritmos de criptografia só precisam de
 * uma entrada em ENCRYPTION_FIXTURES.
 */
final class AlgorithmContractTest extends TestCase
{
    /** Textos que algoritmos que preservam o texto devem conseguir ida e volta. */
    private const TEXT_SAMPLES = [
        '',
        'Attack at dawn',
        'Olá, mundo! Ação 100% 🚀',
        "  leading and trailing spaces  \n",
    ];

    /**
     * Opções válidas e, quando o algoritmo não preserva qualquer texto,
     * as amostras do seu espaço de mensagens.
     *
     * Hill: só letras maiúsculas, em quantidade múltipla do tamanho do bloco
     * (12 letras servem para blocos de 2, 3, 4 e 6).
     *
     * @var array<string, array{options: array<string, mixed>, samples?: list<string>}>
     */
    private const ENCRYPTION_FIXTURES = [
        'caesar' => ['options' => ['shift' => 7]],
        'vigenere' => ['options' => ['key' => 'LEMON']],
        'hill' => ['options' => ['key' => [[3, 3], [2, 5]]], 'samples' => ['', 'ATTACKATDAWN']],
        'aes-256-gcm' => ['options' => ['key' => 'secret']],
    ];

    private const SECURITY_LEVELS = ['educational', 'modern', 'not-applicable'];

    private const OPERATION_CONTRACTS = [
        EncryptionAlgorithm::class => 'encryption',
        HashAlgorithm::class => 'hashing',
        EncodingAlgorithm::class => 'encoding',
    ];

    public function test_every_algorithm_implements_exactly_one_operation_contract(): void
    {
        foreach ($this->algorithms() as $id => $algorithm) {
            $this->assertCount(
                1,
                $this->operationContractsOf($algorithm),
                "\"{$id}\" must implement exactly one of: ".implode(', ', array_keys(self::OPERATION_CONTRACTS)),
            );
        }
    }

    public function test_metadata_is_consistent_with_the_implemented_contract(): void
    {
        foreach ($this->algorithms() as $id => $algorithm) {
            $metadata = $algorithm->describe();
            $isEncryption = $algorithm instanceof EncryptionAlgorithm;
            $isHash = $algorithm instanceof HashAlgorithm;

            $this->assertSame($id, $metadata->id, "\"{$id}\": registry id and describe()->id differ");
            $this->assertSame(
                self::OPERATION_CONTRACTS[$this->operationContractsOf($algorithm)[0]],
                $metadata->category,
                "\"{$id}\": category does not match its contract",
            );
            $this->assertSame($isEncryption, $metadata->supportsEncryption, "\"{$id}\": supportsEncryption");
            $this->assertSame($isEncryption, $metadata->supportsDecryption, "\"{$id}\": supportsDecryption");
            $this->assertSame($isHash, $metadata->supportsHashing, "\"{$id}\": supportsHashing");
            $this->assertContains($metadata->securityLevel, self::SECURITY_LEVELS, "\"{$id}\": securityLevel");
            $this->assertNotSame('', trim($metadata->name), "\"{$id}\": empty name");
            $this->assertNotSame('', trim($metadata->description), "\"{$id}\": empty description");

            if (! $isEncryption) {
                $this->assertFalse($metadata->requiresKey, "\"{$id}\": only encryption algorithms take a key");
            }

            if ($algorithm instanceof EncodingAlgorithm) {
                $this->assertSame('not-applicable', $metadata->securityLevel, "\"{$id}\": encodings provide no security");
            }
        }
    }

    public function test_every_encryption_algorithm_has_a_contract_fixture(): void
    {
        foreach ($this->algorithms(EncryptionAlgorithm::class) as $id => $algorithm) {
            $this->assertArrayHasKey(
                $id,
                self::ENCRYPTION_FIXTURES,
                "Add \"{$id}\" to ENCRYPTION_FIXTURES so its contract is verified.",
            );
        }
    }

    public function test_decrypt_reverses_encrypt(): void
    {
        foreach ($this->algorithms(EncryptionAlgorithm::class) as $id => $algorithm) {
            $fixture = self::ENCRYPTION_FIXTURES[$id] ?? $this->fail("Missing fixture for \"{$id}\"");

            foreach ($fixture['samples'] ?? self::TEXT_SAMPLES as $plaintext) {
                $ciphertext = $algorithm->encrypt($plaintext, $fixture['options']);

                $this->assertSame(
                    $plaintext,
                    $algorithm->decrypt($ciphertext, $fixture['options']),
                    "\"{$id}\": decrypt(encrypt(x)) !== x for x = ".json_encode($plaintext),
                );
            }
        }
    }

    public function test_encryption_without_options_fails_with_a_domain_exception(): void
    {
        // Opções ausentes devem virar InvalidAlgorithmOptionsException (422),
        // nunca um erro genérico (500).
        foreach ($this->algorithms(EncryptionAlgorithm::class) as $id => $algorithm) {
            foreach (['encrypt', 'decrypt'] as $operation) {
                try {
                    $algorithm->{$operation}('ABCD', []);
                    $this->fail("\"{$id}\": {$operation}() without options should throw");
                } catch (InvalidAlgorithmOptionsException) {
                    $this->addToAssertionCount(1);
                }
            }
        }
    }

    public function test_decode_reverses_encode_for_any_bytes(): void
    {
        $samples = [...self::TEXT_SAMPLES, "\x00\x01\xfe\xff", random_bytes(64)];

        foreach ($this->algorithms(EncodingAlgorithm::class) as $id => $algorithm) {
            foreach ($samples as $data) {
                $this->assertSame(
                    $data,
                    $algorithm->decode($algorithm->encode($data)),
                    "\"{$id}\": decode(encode(x)) !== x for x = ".bin2hex($data),
                );
            }
        }
    }

    public function test_hash_is_deterministic_fixed_length_lowercase_hex(): void
    {
        foreach ($this->algorithms(HashAlgorithm::class) as $id => $algorithm) {
            $digests = array_map(fn (string $input) => $algorithm->hash($input), self::TEXT_SAMPLES);

            $this->assertSame($digests[1], $algorithm->hash(self::TEXT_SAMPLES[1]), "\"{$id}\": not deterministic");
            $this->assertCount(count($digests), array_unique($digests), "\"{$id}\": different inputs collided");
            $this->assertCount(1, array_unique(array_map('strlen', $digests)), "\"{$id}\": digest length varies");

            foreach ($digests as $digest) {
                $this->assertMatchesRegularExpression('/\A[0-9a-f]+\z/', $digest, "\"{$id}\": digest is not lowercase hex");
            }
        }
    }

    /**
     * @param  class-string|null  $contract  filtra pelos algoritmos que implementam este contrato.
     * @return array<string, DescribesAlgorithm>
     */
    private function algorithms(?string $contract = null): array
    {
        $registry = $this->app->make(Registry::class);
        $algorithms = [];

        foreach ($registry->all() as $metadata) {
            $algorithm = $registry->get($metadata->id);

            if ($contract === null || $algorithm instanceof $contract) {
                $algorithms[$metadata->id] = $algorithm;
            }
        }

        $this->assertNotEmpty($algorithms, 'No algorithms found for '.($contract ?? 'any contract'));

        return $algorithms;
    }

    /**
     * @return list<class-string>
     */
    private function operationContractsOf(DescribesAlgorithm $algorithm): array
    {
        return array_values(array_filter(
            array_keys(self::OPERATION_CONTRACTS),
            fn (string $contract) => $algorithm instanceof $contract,
        ));
    }
}
