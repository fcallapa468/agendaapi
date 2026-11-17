<?php

namespace App\Http\Resources\V2;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\User
 */
class UsuarioResource extends JsonResource
{
    // Forma en que la v2 muestra un usuario (campos en español y con sus contactos)
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->name,
            'correo' => $this->email,
            'registrado' => $this->created_at?->format('Y-m-d'),
            'total_contactos' => $this->whenCounted('contactos'),
            'contactos' => ContactoResource::collection($this->whenLoaded('contactos')),
        ];
    }
}
