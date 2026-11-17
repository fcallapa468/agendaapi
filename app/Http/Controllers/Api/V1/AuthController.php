<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

#[Group('Autenticación', weight: 0)]
class AuthController extends Controller
{
    /**
     * Registrarse
     *
     * Crea un usuario nuevo y devuelve su token de acceso (201).
     */
    public function registro(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        $user = User::create($datos);
        $token = $user->createToken('registro')->plainTextToken;

        return response()->json([
            'usuario' => $user,
            'token' => $token,
            'tipo' => 'Bearer',
        ], 201);
    }

    /**
     * Iniciar sesión
     *
     * Devuelve un token de acceso si el correo y la contraseña son correctos (200),
     * o 401 si no lo son. Se permiten 5 intentos por minuto; luego responde 429.
     */
    public function login(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $datos['email'])->first();

        if (! $user || ! Hash::check($datos['password'], $user->password)) {
            return response()->json([
                'error' => [
                    'codigo' => 401,
                    'titulo' => 'Credenciales incorrectas',
                    'detalle' => 'El correo o la contraseña no son correctos.',
                ],
            ], 401);
        }

        $token = $user->createToken('login')->plainTextToken;

        return response()->json([
            'usuario' => $user,
            'token' => $token,
            'tipo' => 'Bearer',
        ], 200);
    }

    /**
     * Ver mi perfil
     *
     * Devuelve el usuario dueño del token.
     */
    public function perfil(Request $request): JsonResponse
    {
        return response()->json($request->user()->loadCount('contactos'), 200);
    }

    /**
     * Cerrar sesión
     *
     * Anula el token con el que se hizo la petición (204). Después ese token ya no sirve.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(null, 204);
    }
}
