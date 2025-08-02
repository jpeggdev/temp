<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\Request\UpdateBusinessRolePermissionDTO;
use App\Service\UpdateBusinessRolePermissionService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class UpdateBusinessRolePermissionController extends ApiController
{
    public function __construct(
        private readonly UpdateBusinessRolePermissionService $updateBusinessRolePermissionService,
    ) {
    }

    #[Route(
        '/business-role/update-permissions',
        name: 'api_business_role_update_permissions',
        methods: ['PUT']
    )]
    public function __invoke(
        #[MapRequestPayload] UpdateBusinessRolePermissionDTO $dto,
    ): Response {
        $this->updateBusinessRolePermissionService->updateBusinessRolePermissions($dto);

        return $this->createSuccessResponse(['message' => 'Business role permissions updated successfully.']);
    }
}
