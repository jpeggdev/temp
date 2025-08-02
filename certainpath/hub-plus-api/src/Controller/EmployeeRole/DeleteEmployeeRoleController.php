<?php

declare(strict_types=1);

namespace App\Controller\EmployeeRole;

use App\Controller\ApiController;
use App\Entity\EmployeeRole;
use App\Security\Voter\EmployeeRoleSecurityVoter;
use App\Service\EmployeeRole\DeleteEmployeeRoleService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class DeleteEmployeeRoleController extends ApiController
{
    public function __construct(
        private readonly DeleteEmployeeRoleService $deleteEmployeeRoleService,
    ) {
    }

    #[Route('/employee/role/{id}/delete', name: 'api_employee_role_delete', methods: ['DELETE'])]
    public function __invoke(EmployeeRole $employeeRole): Response
    {
        $this->denyAccessUnlessGranted(
            EmployeeRoleSecurityVoter::EMPLOYEE_ROLE_MANAGE,
            $employeeRole
        );
        $this->deleteEmployeeRoleService->deleteRole($employeeRole);

        return $this->createSuccessResponse(['message' => 'Employee Role deleted successfully.']);
    }
}
