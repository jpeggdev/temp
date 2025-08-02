<?php

declare(strict_types=1);

namespace App\Controller\Employee;

use App\Controller\ApiController;
use App\DTO\Request\UpdateEmployeeApplicationAccessDTO;
use App\Entity\Employee;
use App\Service\Employee\UpdateEmployeeApplicationAccessService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class UpdateEmployeeApplicationAccessController extends ApiController
{
    public function __construct(
        private readonly UpdateEmployeeApplicationAccessService $createUpdateEmployeeApplicationAccessService,
    ) {
    }

    #[Route(
        '/user/{uuid}/update-employee-application-access',
        name: 'api_employee_update_application_access',
        methods: ['PUT']
    )]
    public function createUpdateEmployeeApplicationAccess(
        Employee $employee,
        #[MapRequestPayload] UpdateEmployeeApplicationAccessDTO $dto,
    ): Response {
        $this->createUpdateEmployeeApplicationAccessService->updateEmployeeApplicationAccess($employee, $dto);

        return $this->createSuccessResponse(['message' => 'Employee application access updated successfully.']);
    }
}
