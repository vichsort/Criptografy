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
        // The status route (/) uses the "api" group, not "web": the app has
        // no pages, so it needs neither sessions nor encrypted cookies (which
        // would require APP_KEY).
        then: fn () => Route::middleware('api')->group(__DIR__.'/../routes/status.php'),
    )
    ->withMiddleware(function (Middleware $middleware) {
        // In production (Render) every request arrives through the platform's
        // proxy. Trusting X-Forwarded-For makes $request->ip() the client's
        // real IP, which the rate limit below needs. This is only safe
        // because the app is never exposed without a proxy in front of it.
        $middleware->trustProxies(at: '*');

        // Per-IP limit defined in AppServiceProvider (the "api" RateLimiter).
        $middleware->throttleApi();

        // Whitespace in "input" and in the options (e.g. keys) is data, not noise.
        $middleware->trimStrings(except: [
            'input',
            'options.*',
        ]);

        $middleware->convertEmptyStringsToNull(except: [
            fn (Request $request) => $request->is('api/*'),
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Every error response under /api/* is JSON, even when the client does
        // not send "Accept: application/json" (the browser's fetch() does not).
        // Otherwise validation failures become 302 redirects and 404/405 HTML.
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // Errors caused by client input are not application failures: they
        // become 4xx responses but are not written to the log.
        $exceptions->dontReport([
            AlgorithmNotFoundException::class,
            UnsupportedOperationException::class,
            InvalidAlgorithmOptionsException::class,
            NonTextResultException::class,
        ]);

        // Turns cryptography domain errors into consistent JSON responses,
        // so controllers never need to know HTTP status codes.
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
