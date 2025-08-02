<?php

declare(strict_types=1);

namespace App\Controller\Employee;

use App\Controller\ApiController;
use App\DTO\Request\UpdateEmployeeBusinessRoleDTO;
use App\Entity\Employee;
use App\Service\Employee\UpdateEmployeeBusinessRoleService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class UpdateEmployeeBusinessRoleController extends ApiController
{
    public function __construct(private readonly UpdateEmployeeBusinessRoleService $updateEmployeeBusinessRoleService)
    {
    }

    #[Route('/user/{uuid}/update-employee-business-role', name: 'api_employee_update_business_role', methods: ['PUT'])]
    public function updateEmployeeBusinessRole(
        Employee $employee,
        #[MapRequestPayload] UpdateEmployeeBusinessRoleDTO $dto,
    ): Response {
        $this->updateEmployeeBusinessRoleService->updateEmployeeBusinessRole($employee, $dto);

        return $this->createSuccessResponse(['message' => 'Employee business role updated successfully.']);
    }
}
