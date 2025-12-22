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
        $middleware->alias([
            'auth' => \Illuminate\Auth\Middleware\Authenticate::class,
            'guest' => \Illuminate\Auth\Middleware\RedirectIfAuthenticated::class,
            'super_admin' => \App\Http\Middleware\EnsureSuperAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->renderable(function (\Illuminate\Http\Exceptions\PostTooLargeException $e, $request) {
            if ($request->is('booking*')) {
                return back()->withErrors([
                    'document' => 'File yang Anda upload terlalu besar. Maksimal ukuran file adalah 2MB. Silakan compress file PDF Anda terlebih dahulu.'
                ])->withInput();
            }
            
            return response()->view('errors.post-too-large', [], 413);
        });
    })->create();
