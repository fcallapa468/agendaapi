<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\UsuarioResource;
use App\Models\User;
use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\QueryParameter;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

#[Group('Usuarios')]
class UserController extends Controller
{
    /**
     * Listar usuarios
     *
     * Devuelve los usuarios por páginas, con el total de contactos de cada uno.
     */
    #[QueryParameter('buscar', description: 'Texto a buscar en el nombre o el correo.', type: 'string', example: 'mario')]
    #[QueryParameter('por_pagina', description: 'Registros por página (de 1 a 50).', type: 'int', default: 10)]
    #[QueryParameter('page', description: 'Número de página.', type: 'int', default: 1)]
    public function index(Request $request): AnonymousResourceCollection
    {
        $consulta = User::query()->withCount('contactos');

        if ($request->filled('buscar')) {
            $texto = '%'.$request->input('buscar').'%';
            $consulta->where(function ($q) use ($texto) {
                $q->where('name', 'ilike', $texto)->orWhere('email', 'ilike', $texto);
            });
        }

        $porPagina = min(max((int) $request->input('por_pagina', 10), 1), 50);

        $usuarios = $consulta->orderBy('id')->paginate($porPagina)->withQueryString();

        return UsuarioResource::collection($usuarios);
    }

    /**
     * Ver un usuario con sus contactos
     *
     * Responde 404 si el usuario no existe.
     */
    public function show(User $user): UsuarioResource
    {
        $user->load('contactos')->loadCount('contactos');

        return new UsuarioResource($user);
    }
}
