<?php

namespace App\Http\Controllers;

use App\Cryptography\Registry;
use Illuminate\Http\JsonResponse;

final class AlgorithmController extends Controller
{
    public function __construct(
        private readonly Registry $registry,
    ) {}

    public function index(): JsonResponse
    {
        return response()->json([
            'data' => $this->registry->all(),
        ]);
    }
}
