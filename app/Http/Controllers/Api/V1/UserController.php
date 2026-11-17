<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\QueryParameter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @deprecated Use la versión 2 (/api/v2/users). La versión 1 se apagará el 31/12/2026.
 */
#[Group('Usuarios')]
class UserController extends Controller
{
    /**
     * Listar usuarios
     *
     * Devuelve los usuarios por páginas. Permite buscar y ordenar.
     */
    #[QueryParameter('buscar', description: 'Texto a buscar en el nombre o el correo.', type: 'string', example: 'mario')]
    #[QueryParameter('orden', description: 'Campo para ordenar: id, name, email o created_at.', type: 'string', default: 'id')]
    #[QueryParameter('dir', description: 'Dirección del orden: asc o desc.', type: 'string', default: 'asc')]
    #[QueryParameter('por_pagina', description: 'Registros por página (de 1 a 50).', type: 'int', default: 10)]
    #[QueryParameter('page', description: 'Número de página.', type: 'int', default: 1)]
    public function index(Request $request): JsonResponse
    {
        $consulta = User::query();

        // Filtro: busca el texto en el nombre o en el correo
        if ($request->filled('buscar')) {
            $texto = '%'.$request->input('buscar').'%';
            $consulta->where(function ($q) use ($texto) {
                $q->where('name', 'ilike', $texto)->orWhere('email', 'ilike', $texto);
            });
        }

        // Orden: solo se permite ordenar por estos campos
        $orden = $request->input('orden', 'id');
        if (! in_array($orden, ['id', 'name', 'email', 'created_at'])) {
            $orden = 'id';
        }
        $direccion = $request->input('dir') === 'desc' ? 'desc' : 'asc';

        // Paginación: entre 1 y 50 registros por página (10 por defecto)
        $porPagina = min(max((int) $request->input('por_pagina', 10), 1), 50);

        $usuarios = $consulta->orderBy($orden, $direccion)
            ->paginate($porPagina)
            ->withQueryString();

        return response()->json($usuarios, 200);
    }

    /**
     * Crear un usuario
     *
     * Requiere token. Responde 201 con el usuario creado, o 422 si los datos no cumplen las reglas.
     */
    public function store(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        $user = User::create($datos);

        return response()->json($user, 201);
    }

    /**
     * Ver un usuario
     *
     * Responde 404 si el usuario no existe.
     */
    public function show(User $user): JsonResponse
    {
        return response()->json($user, 200);
    }

    /**
     * Modificar un usuario
     *
     * Requiere token. Cada usuario solo puede modificarse a sí mismo (si no, 403).
     */
    public function update(Request $request, User $user): JsonResponse
    {
        abort_if($request->user()->id !== $user->id, 403);

        $datos = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:users,email,'.$user->id,
            'password' => 'sometimes|required|string|min:8',
        ]);

        $user->update($datos);

        return response()->json($user, 200);
    }

    /**
     * Eliminar un usuario
     *
     * Requiere token. Cada usuario solo puede eliminarse a sí mismo (si no, 403).
     * Responde 204 sin contenido y también se borran sus contactos.
     */
    public function destroy(Request $request, User $user): JsonResponse
    {
        abort_if($request->user()->id !== $user->id, 403);

        $user->delete();

        return response()->json(null, 204);
    }
}
