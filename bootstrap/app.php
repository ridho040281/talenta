<?php

use App\Http\Middleware\RoleMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');
        $middleware->alias([
            'role' => RoleMiddleware::class,
        ]);
        $middleware->validateCsrfTokens(except: [
            'badminton/matches/*/score',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->is('*/api/*') || $request->expectsJson(),
        );
        $exceptions->render(function (MethodNotAllowedHttpException $e, Request $request) {
            if ($request->is('peserta/collective/*')) {
                return redirect()->route('peserta.collective.wizard')
                    ->with('info', 'Sesi pratinjau telah berakhir atau halaman dimuat ulang. Silakan pilih dan unggah kembali file Excel Anda.');
            }
        });
    })->create();
