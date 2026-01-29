<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \Illuminate\Database\Eloquent\Model
 */
class RoleResource extends JsonResource
{
    /**
     * Transform the resource into an array. Expose id, name et permissions
     * (noms uniquement) si chargées. Exclut guard_name, timestamps, pivot.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->getAttribute('id'),
            'name' => $this->resource->getAttribute('name'),
            'permissions' => $this->whenLoaded(
                'permissions',
                fn () => $this->resource->getRelation('permissions')->pluck('name')->values()->all()
            ),
        ];
    }
}
