<?php

declare(strict_types=1);

namespace App\Controller\EmployeeRole;

use App\Controller\ApiController;
use App\DTO\Request\EmployeeRole\CreateUpdateEmployeeRoleDTO;
use App\Entity\EmployeeRole;
use App\Security\Voter\EmployeeRoleSecurityVoter;
use App\Service\EmployeeRole\UpdateEmployeeRoleService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class UpdateEmployeeRoleController extends ApiController
{
    public function __construct(
        private readonly UpdateEmployeeRoleService $updateEmployeeRoleService,
    ) {
    }

    #[Route('/employee/role/{id}', name: 'api_employee_role_update', methods: ['PUT'])]
    public function __invoke(
        EmployeeRole $employeeRole,
        #[MapRequestPayload] CreateUpdateEmployeeRoleDTO $dto,
    ): Response {
        $this->denyAccessUnlessGranted(EmployeeRoleSecurityVoter::EMPLOYEE_ROLE_MANAGE);
        $roleResponse = $this->updateEmployeeRoleService->updateRole($employeeRole, $dto);

        return $this->createSuccessResponse($roleResponse);
    }
}
