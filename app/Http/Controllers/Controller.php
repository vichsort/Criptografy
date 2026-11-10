<?php

namespace App\Http\Controllers;

use App\Cryptography\NonTextResultException;
use Illuminate\Http\JsonResponse;

abstract class Controller
{
    /**
     * Standard response of the cryptographic operations: { "result": "..." }.
     *
     * @throws NonTextResultException when the result is not valid UTF-8 and
     *                                therefore cannot be carried in JSON.
     */
    protected function result(string $result): JsonResponse
    {
        if (! mb_check_encoding($result, 'UTF-8')) {
            throw NonTextResultException::make();
        }

        return response()->json(['result' => $result]);
    }
}
