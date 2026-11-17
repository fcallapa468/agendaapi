<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Si una ruta de la API pide token y no lo trae, no redirigir a una página de login
        // (la API no tiene páginas): se responde 401 en JSON.
        $middleware->redirectGuestsTo(fn (Request $request) => $request->is('api/*') ? null : '/');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // Formato único para todos los errores de la API
        $error = fn (int $codigo, string $titulo, string $detalle, array $extra = []) => response()->json([
            'error' => array_merge([
                'codigo' => $codigo,
                'titulo' => $titulo,
                'detalle' => $detalle,
            ], $extra),
        ], $codigo);

        // 422: los datos enviados no cumplen las reglas
        $exceptions->render(function (ValidationException $e, Request $request) use ($error) {
            if ($request->is('api/*')) {
                return $error(422, 'Datos inválidos', 'Uno o más campos no cumplen las reglas.', ['campos' => $e->errors()]);
            }
        });

        // 401: falta el token o no es válido
        $exceptions->render(function (AuthenticationException $e, Request $request) use ($error) {
            if ($request->is('api/*')) {
                return $error(401, 'No autenticado', 'Falta el token de acceso o no es válido.');
            }
        });

        // 403, 404, 405, 429 y demás errores HTTP
        $exceptions->render(function (HttpExceptionInterface $e, Request $request) use ($error) {
            if ($request->is('api/*')) {
                $codigo = $e->getStatusCode();
                $textos = [
                    403 => ['Acceso prohibido', 'No tiene permiso para esta operación.'],
                    404 => ['Recurso no encontrado', 'El recurso solicitado no existe.'],
                    405 => ['Método no permitido', 'Esta dirección no acepta el método '.$request->method().'.'],
                    429 => ['Demasiadas peticiones', 'Superó el límite de peticiones; espere un momento.'],
                ];
                [$titulo, $detalle] = $textos[$codigo] ?? ['Error', 'No se pudo procesar la petición.'];

                // Se conservan las cabeceras del error (por ejemplo Retry-After en el 429)
                return $error($codigo, $titulo, $detalle)->withHeaders($e->getHeaders());
            }
        });
    })->create();
