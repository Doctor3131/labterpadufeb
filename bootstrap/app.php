<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

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
