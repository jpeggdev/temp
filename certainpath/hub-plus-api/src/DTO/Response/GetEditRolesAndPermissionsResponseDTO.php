<?php

declare(strict_types=1);

namespace App\DTO\Response;

class GetEditRolesAndPermissionsResponseDTO
{
    public array $roles;
    public array $permissions;

    public function __construct(array $roles, array $permissions)
    {
        $this->roles = $roles;
        $this->permissions = $permissions;
    }
}
