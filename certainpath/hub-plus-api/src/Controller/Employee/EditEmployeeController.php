<?php

declare(strict_types=1);

namespace App\Controller\Employee;

use App\Controller\ApiController;
use App\DTO\Request\Employee\EditEmployeeDTO;
use App\Entity\Employee;
use App\Service\Employee\EditEmployeeService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class EditEmployeeController extends ApiController
{
    public function __construct(private readonly EditEmployeeService $editEmployeeService)
    {
    }

    #[Route('/employees/{uuid}/edit', name: 'api_employee_edit', methods: ['PUT'])]
    public function __invoke(Employee $employee, #[MapRequestPayload] EditEmployeeDTO $editEmployeeDTO): Response
    {
        $updatedUser = $this->editEmployeeService->editEmployee($employee, $editEmployeeDTO);

        return $this->createSuccessResponse($updatedUser);
    }
}
