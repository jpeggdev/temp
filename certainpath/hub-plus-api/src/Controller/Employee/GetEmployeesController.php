<?php

declare(strict_types=1);

// region Init

namespace App\Controller\Employee;

use App\Controller\ApiController;
use App\DTO\LoggedInUserDTO;
use App\DTO\Request\UserQueryDTO;
use App\Service\Employee\EmployeeQueryService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

// endregion

#[Route(path: '/api/private')]
class GetEmployeesController extends ApiController
{
    // region Declarations
    public function __construct(private readonly EmployeeQueryService $employeeQueryService)
    {
    }
    // endregion

    // region __invoke
    #[Route('/employees', name: 'api_employees_get', methods: ['GET'])]
    public function __invoke(
        #[MapQueryString] UserQueryDTO $queryDto,
        LoggedInUserDTO $loggedInUserDTO,
        Request $request,
    ): Response {
        $usersData = $this->employeeQueryService->getEmployees(
            $queryDto,
            $loggedInUserDTO->getActiveCompany()
        );

        return $this->createSuccessResponse(
            $usersData,
            $usersData['totalCount']
        );
    }
    // endregion
}
