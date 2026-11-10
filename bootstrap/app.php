<?php

use App\Cryptography\AlgorithmNotFoundException;
use App\Cryptography\Contracts\InvalidAlgorithmOptionsException;
use App\Cryptography\NonTextResultException;
use App\Cryptography\UnsupportedOperationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        // A rota de status (/) usa o grupo "api", não o "web": a aplicação
        // não tem páginas, então não precisa de sessão nem de cookies
        // criptografados (que exigiriam APP_KEY).
        then: fn () => Route::middleware('api')->group(__DIR__.'/../routes/status.php'),
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Em produção (Render) toda requisição chega pelo proxy da
        // plataforma. Confiar no X-Forwarded-For faz $request->ip() ser o IP
        // real do cliente, o que o rate limit abaixo precisa. Só é seguro
        // porque a aplicação nunca fica exposta sem um proxy na frente.
        $middleware->trustProxies(at: '*');

        // Limite por IP definido em AppServiceProvider (RateLimiter "api").
        $middleware->throttleApi();

        // Espaços em "input" e nas opções (ex.: chaves) são dados, não ruído.
        $middleware->trimStrings(except: [
            'input',
            'options.*',
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

        // Erros causados pela entrada do cliente não são falhas da aplicação:
        // viram resposta 4xx, mas não vão para o log.
        $exceptions->dontReport([
            AlgorithmNotFoundException::class,
            UnsupportedOperationException::class,
            InvalidAlgorithmOptionsException::class,
            NonTextResultException::class,
        ]);

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

        $exceptions->render(function (NonTextResultException $e, Request $request) {
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
