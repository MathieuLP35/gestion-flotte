<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \Illuminate\Database\Eloquent\Model
 */
class PermissionResource extends JsonResource
{
    /**
     * Transform the resource into an array. Expose uniquement id et name.
     * Exclut guard_name, timestamps, pivot, roles.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->getAttribute('id'),
            'name' => $this->resource->getAttribute('name'),
        ];
    }
}
