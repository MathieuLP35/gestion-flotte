<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array. Expose uniquement les champs
     * nécessaires au frontend (auth, layout, forms, listes). Exclut password,
     * timestamps, email_verified_at, pivot. agence, roles, role_id via whenLoaded.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'agence_id' => $this->agence_id,
            'agence' => $this->whenLoaded('agence', fn () => $this->agence ? ['id' => $this->agence->id, 'nom' => $this->agence->nom] : null),
            'roles' => $this->whenLoaded('roles', fn () => $this->roles->pluck('name')->values()->all()),
            'role_id' => $this->whenLoaded('roles', fn () => $this->roles->first()?->id),
        ];
    }
}
