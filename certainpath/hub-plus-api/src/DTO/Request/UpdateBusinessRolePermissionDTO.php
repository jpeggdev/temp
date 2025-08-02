<?php

declare(strict_types=1);

namespace App\DTO\Request;

use Symfony\Component\Validator\Constraints as Assert;

readonly class UpdateBusinessRolePermissionDTO
{
    public function __construct(
        #[Assert\NotNull]
        #[Assert\Positive]
        public int $roleId,
        #[Assert\NotNull]
        #[Assert\Type('array')]
        #[Assert\All([
            new Assert\Type('int'),
            new Assert\Positive(),
        ])]
        public array $permissionIds,
    ) {
    }
}
