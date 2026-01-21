<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'email_verified_at' => $this->email_verified_at,
            'role' => $this->whenLoaded('role', function () {
                return new RoleResource($this->role);
            }),
            'permissions' => $this->getPermissions()->values()->toArray(),
            'can_edit' => $this->isWebadmin(), // Webadmin can edit
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
