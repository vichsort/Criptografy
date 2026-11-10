<?php

namespace Tests\Unit\Http;

use App\Http\Requests\Cryptography\CryptographyOperationRequest;
use Tests\TestCase;

final class CryptographyOperationRequestTest extends TestCase
{
    public function test_options_returns_every_option_sent(): void
    {
        $request = CryptographyOperationRequest::create('/api/encrypt', 'POST', [
            'options' => ['key' => 'LEMON', 'shift' => 3],
        ]);

        $this->assertSame(['key' => 'LEMON', 'shift' => 3], $request->options());
    }

    public function test_options_defaults_to_an_empty_array(): void
    {
        $this->assertSame([], CryptographyOperationRequest::create('/api/hash', 'POST')->options());
    }
}
