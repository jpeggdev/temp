<?php

declare(strict_types=1);

namespace App\Controller\EmployeeRole;

use App\Controller\ApiController;
use App\Entity\EmployeeRole;
use App\Security\Voter\EmployeeRoleSecurityVoter;
use App\Service\EmployeeRole\GetEditEmployeeRoleService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class GetEditEmployeeRoleController extends ApiController
{
    public function __construct(
        private readonly GetEditEmployeeRoleService $getEditEmployeeRoleService,
    ) {
    }

    #[Route('/employee/role/{id}', name: 'api_employee_role_edit_details', methods: ['GET'])]
    public function __invoke(EmployeeRole $employeeRole): Response
    {
        $this->denyAccessUnlessGranted(EmployeeRoleSecurityVoter::EMPLOYEE_ROLE_MANAGE, $employeeRole);
        $roleDetails = $this->getEditEmployeeRoleService->getEditEmployeeRoleDetails($employeeRole);

        return $this->createSuccessResponse($roleDetails);
    }
}
