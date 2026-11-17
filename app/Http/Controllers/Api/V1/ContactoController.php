<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Contacto;
use App\Models\User;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

#[Group('Contactos')]
class ContactoController extends Controller
{
    /**
     * Listar los contactos de un usuario
     *
     * Devuelve los contactos ordenados por nombre, 10 por página.
     */
    public function index(User $user): JsonResponse
    {
        $contactos = $user->contactos()->orderBy('nombre')->paginate(10);

        return response()->json($contactos, 200);
    }

    /**
     * Agregar un contacto a un usuario
     *
     * Requiere token. Solo se pueden agregar contactos a uno mismo (si no, 403).
     * El correo es opcional. Responde 201 con el contacto creado.
     */
    public function store(Request $request, User $user): JsonResponse
    {
        abort_if($request->user()->id !== $user->id, 403);

        $datos = $request->validate([
            'nombre' => 'required|string|max:100',
            'telefono' => 'required|string|max:20',
            'email' => 'nullable|string|email|max:255',
        ]);

        $contacto = $user->contactos()->create($datos);

        return response()->json($contacto, 201);
    }

    /**
     * Ver un contacto
     */
    public function show(Contacto $contacto): JsonResponse
    {
        return response()->json($contacto, 200);
    }

    /**
     * Modificar un contacto
     *
     * Requiere token. Solo el dueño del contacto puede modificarlo (si no, 403).
     */
    public function update(Request $request, Contacto $contacto): JsonResponse
    {
        abort_if($request->user()->id !== $contacto->user_id, 403);

        $datos = $request->validate([
            'nombre' => 'sometimes|required|string|max:100',
            'telefono' => 'sometimes|required|string|max:20',
            'email' => 'nullable|string|email|max:255',
        ]);

        $contacto->update($datos);

        return response()->json($contacto, 200);
    }

    /**
     * Eliminar un contacto
     *
     * Requiere token. Solo el dueño del contacto puede eliminarlo (si no, 403).
     */
    public function destroy(Request $request, Contacto $contacto): JsonResponse
    {
        abort_if($request->user()->id !== $contacto->user_id, 403);

        $contacto->delete();

        return response()->json(null, 204);
    }
}
