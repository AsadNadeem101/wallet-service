<?php

use App\Exceptions\Wallet\CurrencyMismatchException;
use App\Exceptions\Wallet\InsufficientBalanceException;
use App\Exceptions\Wallet\InvalidAmountException;
use App\Exceptions\Wallet\SelfTransferException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Handle validation errors
        $exceptions->render(function (ValidationException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Validation Failed',
                    'message' => 'The given data was invalid.',
                    'errors' => $e->errors(),
                ], 422);
            }
        });

        // Handle model not found errors
        $exceptions->render(function (ModelNotFoundException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Resource Not Found',
                    'message' => 'The requested resource was not found.',
                ], 404);
            }
        });

        // Handle custom wallet exceptions
        $exceptions->render(function (InsufficientBalanceException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Insufficient Balance',
                    'message' => $e->getMessage(),
                ], 422);
            }
        });

        $exceptions->render(function (InvalidAmountException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Invalid Amount',
                    'message' => $e->getMessage(),
                ], 422);
            }
        });

        $exceptions->render(function (CurrencyMismatchException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Currency Mismatch',
                    'message' => $e->getMessage(),
                ], 422);
            }
        });

        $exceptions->render(function (SelfTransferException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Self Transfer Not Allowed',
                    'message' => $e->getMessage(),
                ], 422);
            }
        });

        // Catch-all: Log error, return clean response (NEVER expose internals)
        $exceptions->render(function (\Throwable $e, Request $request) {
            if ($request->expectsJson()) {
                // Log the full error for debugging
                \Log::error('Unhandled exception', [
                    'exception' => get_class($e),
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                ]);

                // Return clean error to client
                return response()->json([
                    'success' => false,
                    'error' => 'Server Error',
                    'message' => 'An unexpected error occurred. Please try again later.',
                ], 500);
            }
        });
    })->create();
