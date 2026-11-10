<?php

namespace App\Http\Controllers;

use App\Cryptography\Contracts\EncodingAlgorithm;
use App\Cryptography\Registry;
use App\Cryptography\UnsupportedOperationException;
use App\Http\Requests\Cryptography\CryptographyOperationRequest;
use Illuminate\Http\JsonResponse;

final class DecodeController extends Controller
{
    public function __construct(
        private readonly Registry $registry,
    ) {}

    public function store(CryptographyOperationRequest $request): JsonResponse
    {
        $algorithmId = $request->string('algorithm')->toString();
        $algorithm = $this->registry->get($algorithmId);

        if (! $algorithm instanceof EncodingAlgorithm) {
            throw UnsupportedOperationException::forOperation($algorithmId, 'decode');
        }

        $result = $algorithm->decode($request->string('input')->toString());

        return $this->result($result);
    }
}
