<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Throwable;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {

        // گزارش لاگ خطاها
        $exceptions->report(function (Throwable $e) {
            logger()->error($e);
        });

        // تبدیل exception ها به response مناسب API
        $exceptions->render(function (Throwable $e, $request) {

            // فقط برای API
            if ($request->is('api/*')) {

                if ($e instanceof AuthenticationException) {
                    return response()->json([
                        'message' => 'Unauthenticated.'
                    ], 401);
                }

                if ($e instanceof ValidationException) {
                    return response()->json([
                        'message' => 'Validation failed.',
                        'errors' => $e->errors()
                    ], 422);
                }

                if ($e instanceof \InvalidArgumentException) {
                    return response()->json([
                        'message' => $e->getMessage()
                    ], 400);
                }

                if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                    return response()->json([
                        'message' => 'Resource not found.'
                    ], 404);
                }

                // بقیه خطاها → 500
                   return response()->json([
                    'message' => $e->getMessage()
                ], $e instanceof HttpException ? $e->getStatusCode() : 500);
            }

            return null; 
        });
    })->create();
