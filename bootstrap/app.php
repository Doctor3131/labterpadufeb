<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Database\QueryException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
	$middleware->trustProxies(at: '*');
        $middleware->alias([
            'auth' => \Illuminate\Auth\Middleware\Authenticate::class,
            'guest' => \Illuminate\Auth\Middleware\RedirectIfAuthenticated::class,
            'admin' => \App\Http\Middleware\EnsureAdmin::class,
            'super_admin' => \App\Http\Middleware\EnsureSuperAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->renderable(function (QueryException $exception, $request) {
            if (! $request->routeIs('admin.schedules.calendar.change')) {
                return null;
            }

            report($exception);
            $message = str_contains($exception->getMessage(), 'schedule_occurrences')
                ? 'Pertemuan ini sudah memiliki perubahan. Muat ulang kalender, lalu coba lagi.'
                : 'Perubahan jadwal tidak dapat disimpan. Silakan coba lagi.';

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 409);
            }

            return back()->with('error', $message);
        });

        $exceptions->renderable(function (\Illuminate\Http\Exceptions\PostTooLargeException $e, $request) {
            $errorMessage = 'File yang Anda upload terlalu besar. Maksimal ukuran file adalah 5MB. Silakan compress file PDF Anda terlebih dahulu.';

            if ($request->is('booking*')) {
                return back()->withErrors([
                    'document' => $errorMessage
                ])->withInput();
            }

            if ($request->is('refinitiv*')) {
                return back()->withErrors([
                    'statement_file' => $errorMessage,
                    'ktm_file' => $errorMessage,
                ])->withInput();
            }

            if ($request->is('bps*')) {
                return back()->withErrors([
                    'document' => $errorMessage
                ])->withInput();
            }

            return back()->withErrors([
                'file' => $errorMessage
            ])->withInput();
        });
    })->create();
