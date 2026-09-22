<?php

namespace App\Http\Controllers;

use App\Cryptography\Contracts\EncryptionAlgorithm;
use App\Cryptography\Registry;
use App\Cryptography\UnsupportedOperationException;
use App\Http\Requests\Cryptography\CryptographyOperationRequest;
use Illuminate\Http\JsonResponse;

final class EncryptController extends Controller
{
    public function __construct(
        private readonly Registry $registry,
    ) {}

    public function store(CryptographyOperationRequest $request): JsonResponse
    {
        $algorithmId = $request->string('algorithm')->toString();
        $algorithm = $this->registry->get($algorithmId);

        if (! $algorithm instanceof EncryptionAlgorithm) {
            throw UnsupportedOperationException::forOperation($algorithmId, 'encrypt');
        }

        $result = $algorithm->encrypt(
            $request->string('input')->toString(),
            $request->options(),
        );

        return response()->json(['result' => $result]);
    }
}
