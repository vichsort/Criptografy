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
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

/**
 * The only place in the application that knows the full list of available
 * algorithms. Adding a new algorithm (Open/Closed) means registering one more
 * line here, without changing the Registry, the controllers or any existing
 * algorithm.
 */
final class CryptographyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Registry::class, function (Application $app): Registry {
            $registry = new Registry;

            $registry->register($app->make(Caesar::class));
            $registry->register($app->make(Vigenere::class));
            $registry->register($app->make(Hill::class));
            $registry->register($app->make(Aes256Gcm::class));
            $registry->register($app->make(Sha256::class));
            $registry->register($app->make(Sha512::class));
            $registry->register($app->make(Base64::class));
            $registry->register($app->make(Hex::class));

            return $registry;
        });
    }
}
