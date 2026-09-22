<?php

namespace App\Providers;

use App\Cryptography\Aes256Gcm\Aes256Gcm;
use App\Cryptography\Base64\Base64;
use App\Cryptography\Caesar\Caesar;
use App\Cryptography\Hex\Hex;
use App\Cryptography\Hill\Hill;
use App\Cryptography\Registry;
use App\Cryptography\Sha256\Sha256;
use App\Cryptography\Sha512\Sha512;
use App\Cryptography\Vigenere\Vigenere;
use Illuminate\Support\ServiceProvider;

/**
 * Único lugar da aplicação que conhece a lista completa de algoritmos
 * disponíveis. Adicionar um novo algoritmo (Seção 20 — Open/Closed)
 * significa registrar mais uma linha aqui, sem alterar Registry,
 * controllers ou qualquer outro algoritmo já existente.
 */
final class CryptographyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Registry::class, function ($app): Registry {
            $registry = new Registry();

            $registry->register('caesar', $app->make(Caesar::class));
            $registry->register('vigenere', $app->make(Vigenere::class));
            $registry->register('hill', $app->make(Hill::class));
            $registry->register('aes-256-gcm', $app->make(Aes256Gcm::class));
            $registry->register('sha-256', $app->make(Sha256::class));
            $registry->register('sha-512', $app->make(Sha512::class));
            $registry->register('base64', $app->make(Base64::class));
            $registry->register('hex', $app->make(Hex::class));

            return $registry;
        });
    }
}
