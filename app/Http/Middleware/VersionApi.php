<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VersionApi
{
    // Agrega a cada respuesta cabeceras que indican la versión de la API.
    // Si la versión es la 1, avisa además que está obsoleta y cuál la reemplaza.
    public function handle(Request $request, Closure $next, string $version): Response
    {
        $response = $next($request);

        $response->headers->set('X-API-Version', $version);

        if ($version === '1') {
            $response->headers->set('Deprecation', '@'.strtotime('2026-11-16 00:00:00 UTC'));
            $response->headers->set('Sunset', 'Thu, 31 Dec 2026 23:59:59 GMT');
            $response->headers->set('Link', '<'.url('/api/v2/users').'>; rel="successor-version"');
        }

        return $response;
    }
}
