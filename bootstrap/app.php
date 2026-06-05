<?php

use App\Http\Middleware\AxiosMiddleware;
use App\Http\Middleware\CompleteProfile;
use Illuminate\Foundation\Application;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'complete-info' => CompleteProfile::class,
            'axios'         => AxiosMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (AuthorizationException $e, Request $request) {
            $message = 'No tienes autorizacion para realizar esta accion.';

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 403);
            }

            $previous = url()->previous();
            $target = $previous !== $request->fullUrl() ? $previous : route('dashboard');

            return redirect()
                ->to($target)
                ->with('error', $message);
        });

        $exceptions->render(function (HttpExceptionInterface $e, Request $request) {
            if ($e->getStatusCode() !== 403) {
                return null;
            }

            $message = 'No tienes autorizacion para realizar esta accion.';

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 403);
            }

            $previous = url()->previous();
            $target = $previous !== $request->fullUrl() ? $previous : route('dashboard');

            return redirect()
                ->to($target)
                ->with('error', $message);
        });

        $exceptions->render(function (TokenMismatchException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'La sesion expiro. Recarga la pagina e intenta de nuevo.',
                ], 419);
            }

            return redirect()
                ->guest(route('login'))
                ->with('status', 'La sesion expiro. Inicia sesion nuevamente.');
        });
    })->create();
