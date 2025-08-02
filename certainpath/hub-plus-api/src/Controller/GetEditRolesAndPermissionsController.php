<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\GetEditRolesAndPermissionsService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class GetEditRolesAndPermissionsController extends ApiController
{
    public function __construct(
        private readonly GetEditRolesAndPermissionsService $getEditRolesAndPermissionsService,
    ) {
    }

    #[Route(
        '/edit-roles-and-permissions',
        name: 'api_get_edit_roles_and_permissions',
        methods: ['GET']
    )]
    public function __invoke(): Response
    {
        $data = $this->getEditRolesAndPermissionsService->getEditRolesAndPermissions();

        return $this->createSuccessResponse($data);
    }
}
