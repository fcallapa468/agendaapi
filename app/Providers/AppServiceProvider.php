<?php

namespace App\Providers;

use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\SecurityDocumentation\MiddlewareAuthSecurityStrategy;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Documentación automática con Scramble: una página por cada versión de la API.
        // Se quita la página general (/docs/api) y se crean /docs/v1 y /docs/v2.
        Scramble::configure()->expose(false);

        Scramble::registerApi('v1', [
            'api_path' => 'api/v1',
            'info' => [
                'version' => '1.0.0',
                'description' => 'Versión 1 de AgendaAPI: usuarios y sus contactos (CRUD completo). Las operaciones que crean, modifican o eliminan requieren un token Bearer (se obtiene en Iniciar sesión). Obsoleta desde el 16/11/2026; se apagará el 31/12/2026. Use la versión 2.',
            ],
            'ui' => ['title' => 'AgendaAPI v1'],
            // Marca con candado las rutas que usan auth:sanctum y permite enviar el token desde la página
            'security_strategy' => MiddlewareAuthSecurityStrategy::class,
        ])->expose(ui: 'docs/v1', document: 'docs/v1.json');

        Scramble::registerApi('v2', [
            'api_path' => 'api/v2',
            'info' => [
                'version' => '2.0.0',
                'description' => 'Versión 2 de AgendaAPI (actual): consulta de usuarios con campos en español, total de contactos y lista de contactos.',
            ],
            'ui' => ['title' => 'AgendaAPI v2'],
        ])->expose(ui: 'docs/v2', document: 'docs/v2.json');
    }
}
