<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ContactoController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V2\ExternaController;
use App\Http\Controllers\Api\V2\UserController as UserControllerV2;
use App\Http\Middleware\VersionApi;
use Illuminate\Support\Facades\Route;

// Versión 1 (obsoleta, se mantiene para no romper a quien ya la usa)
// name('v1.') antepone "v1." al nombre interno de cada ruta (v1.users.index, ...)
Route::prefix('v1')->name('v1.')->middleware(VersionApi::class.':1')->group(function () {

    // Autenticación: públicas (el login permite 5 intentos por minuto)
    Route::post('registro', [AuthController::class, 'registro'])->name('registro');
    Route::post('login', [AuthController::class, 'login'])->middleware('throttle:5,1')->name('login');

    // Consultas: públicas
    Route::apiResource('users', UserController::class)->only(['index', 'show']);
    Route::apiResource('users.contactos', ContactoController::class)->shallow()->only(['index', 'show']);

    // Todo lo que crea, modifica o elimina: requiere token
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('perfil', [AuthController::class, 'perfil'])->name('perfil');
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');
        Route::apiResource('users', UserController::class)->only(['store', 'update', 'destroy']);
        Route::apiResource('users.contactos', ContactoController::class)->shallow()->only(['store', 'update', 'destroy']);
    });
});

// Versión 2 (actual): nuevo formato de respuesta
Route::prefix('v2')->name('v2.')->middleware(VersionApi::class.':2')->group(function () {
    Route::apiResource('users', UserControllerV2::class)->only(['index', 'show']);

    // Datos que AgendaAPI obtiene de otras APIs
    Route::prefix('externas')->name('externas.')->group(function () {
        Route::get('clima', [ExternaController::class, 'clima'])->name('clima');
        Route::get('tipo-cambio', [ExternaController::class, 'tipoCambio'])->name('tipo-cambio');
    });
});
