<?php

namespace App\Http\Controllers;

use App\Cryptography\NonTextResultException;
use Illuminate\Http\JsonResponse;

abstract class Controller
{
    /**
     * Resposta padrão das operações criptográficas: { "result": "..." }.
     *
     * @throws NonTextResultException quando o resultado não é UTF-8 válido
     *                                e, portanto, não cabe em JSON.
     */
    protected function result(string $result): JsonResponse
    {
        if (! mb_check_encoding($result, 'UTF-8')) {
            throw NonTextResultException::make();
        }

        return response()->json(['result' => $result]);
    }
}
