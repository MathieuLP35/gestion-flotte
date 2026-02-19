<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 */
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
        /** @var User $user */
        $user = $this->resource;

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'agence_id' => $user->agence_id,
            'agence' => $this->whenLoaded('agence', function () use ($user): ?array {
                $agence = $user->agence;

                return $agence !== null ? ['id' => $agence->id, 'nom' => $agence->nom] : null;
            }),
            'roles' => $this->whenLoaded('roles', fn () => $user->roles->pluck('name')->values()->all()),
            'role_id' => $this->whenLoaded('roles', fn () => $user->roles->first()?->getAttribute('id')),
        ];
    }
}
