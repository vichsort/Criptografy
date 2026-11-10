<?php

namespace App\Http\Controllers;

use App\Cryptography\Contracts\HashAlgorithm;
use App\Cryptography\Registry;
use App\Cryptography\UnsupportedOperationException;
use App\Http\Requests\Cryptography\CryptographyOperationRequest;
use Illuminate\Http\JsonResponse;

final class HashController extends Controller
{
    public function __construct(
        private readonly Registry $registry,
    ) {}

    public function store(CryptographyOperationRequest $request): JsonResponse
    {
        $algorithmId = $request->string('algorithm')->toString();
        $algorithm = $this->registry->get($algorithmId);

        if (! $algorithm instanceof HashAlgorithm) {
            throw UnsupportedOperationException::forOperation($algorithmId, 'hash');
        }

        $result = $algorithm->hash(
            $request->string('input')->toString(),
            $request->options(),
        );

        return $this->result($result);
    }
}
