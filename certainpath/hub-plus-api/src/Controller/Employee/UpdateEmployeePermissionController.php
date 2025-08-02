<?php

declare(strict_types=1);

namespace App\Controller\Employee;

use App\Controller\ApiController;
use App\DTO\Request\UpdateEmployeePermissionDTO;
use App\Entity\Employee;
use App\Service\Employee\UpdateEmployeePermissionService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class UpdateEmployeePermissionController extends ApiController
{
    public function __construct(private readonly UpdateEmployeePermissionService $updateEmployeePermissionService)
    {
    }

    #[Route('/user/{uuid}/update-employee-permission', name: 'api_employee_update_permission', methods: ['PUT'])]
    public function updateEmployeePermission(
        Employee $employee,
        #[MapRequestPayload] UpdateEmployeePermissionDTO $dto,
    ): Response {
        $this->updateEmployeePermissionService->updateEmployeePermission($employee, $dto);

        return $this->createSuccessResponse(['message' => 'Employee permission updated successfully.']);
    }
}
