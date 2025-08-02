<?php

declare(strict_types=1);

namespace App\Controller\Employee;

use App\Controller\ApiController;
use App\DTO\LoggedInUserDTO;
use App\DTO\Request\Employee\CreateEmployeeDTO;
use App\Service\Employee\CreateEmployeeService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class CreateEmployeeController extends ApiController
{
    public function __construct(
        private readonly CreateEmployeeService $createEmployeeService,
    ) {
    }

    #[Route('/employees/create', name: 'api_employee_create', methods: ['POST'])]
    public function __invoke(
        #[MapRequestPayload] CreateEmployeeDTO $createEmployeeDTO,
        LoggedInUserDTO $loggedInUserDTO,
    ): Response {
        $user = $this->createEmployeeService->createEmployee(
            $createEmployeeDTO,
            $loggedInUserDTO->getActiveCompany()
        );

        return $this->createSuccessResponse($user);
    }
}
