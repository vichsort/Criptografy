<?php

use App\Cryptography\AlgorithmNotFoundException;
use App\Cryptography\Contracts\InvalidAlgorithmOptionsException;
use App\Cryptography\UnsupportedOperationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->trimStrings(except: [
            'input',
        ]);

        $middleware->convertEmptyStringsToNull(except: [
            fn (Request $request) => $request->is('api/*'),
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Toda resposta de erro em /api/* é JSON, mesmo quando o cliente não
        // envia "Accept: application/json" (o fetch() do navegador não envia).
        // Sem isso, falhas de validação viram redirect 302 e 404/405 viram HTML.
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // Traduz os erros de domínio da criptografia (Seção 23) em respostas
        // JSON coerentes para a API, sem que os controllers precisem
        // conhecer códigos de status HTTP.
        $exceptions->render(function (AlgorithmNotFoundException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json(['message' => $e->getMessage()], 404);
            }
        });

        $exceptions->render(function (UnsupportedOperationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json(['message' => $e->getMessage()], 422);
            }
        });

        $exceptions->render(function (InvalidAlgorithmOptionsException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json(['message' => $e->getMessage()], 422);
            }
        });
    })->create();
