<?php

declare(strict_types=1);

namespace App\Controller\EmployeeRole;

use App\Controller\ApiController;
use App\DTO\Request\EmployeeRole\CreateUpdateEmployeeRoleDTO;
use App\Security\Voter\EmployeeRoleSecurityVoter;
use App\Service\EmployeeRole\CreateEmployeeRoleService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class CreateEmployeeRoleController extends ApiController
{
    public function __construct(
        private readonly CreateEmployeeRoleService $createEmployeeRoleService,
    ) {
    }

    #[Route('/employee/role/create', name: 'api_employee_role_create', methods: ['POST'])]
    public function __invoke(
        #[MapRequestPayload] CreateUpdateEmployeeRoleDTO $dto,
    ): Response {
        $this->denyAccessUnlessGranted(EmployeeRoleSecurityVoter::EMPLOYEE_ROLE_MANAGE);
        $roleResponse = $this->createEmployeeRoleService->createRole($dto);

        return $this->createSuccessResponse($roleResponse);
    }
}
