<?php

use Illuminate\Foundation\Application;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Log;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withSchedule(function (Schedule $schedule): void {
        $schedule->command('orders:auto-complete')->hourly();
    })
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->report(function (Throwable $e) {
            Log::error('Unhandled exception', ['exception' => $e]);
        });

        $exceptions->render(function (Throwable $e, $request) {
            if ($request->expectsJson() || $request->ajax()) {
                $debug = (bool) config('app.debug');

                return response()->json([
                    'success' => false,
                    'message' => $debug ? $e->getMessage() : 'Server error. Please try again.',
                ], 500);
            }

            return null;
        });
    })->create();
