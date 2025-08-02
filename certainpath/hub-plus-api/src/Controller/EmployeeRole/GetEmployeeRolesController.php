<?php

declare(strict_types=1);

namespace App\Controller\EmployeeRole;

use App\Controller\ApiController;
use App\DTO\LoggedInUserDTO;
use App\DTO\Request\EmployeeRole\GetEmployeeRolesRequestDTO;
use App\Service\EmployeeRole\GetEmployeeRolesService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class GetEmployeeRolesController extends ApiController
{
    public function __construct(
        private readonly GetEmployeeRolesService $getEmployeeRolesService,
    ) {
    }

    #[Route('/employee-roles', name: 'api_employee_roles_get', methods: ['GET'])]
    public function __invoke(
        #[MapQueryString] GetEmployeeRolesRequestDTO $requestDto,
        LoggedInUserDTO $loggedInUserDTO,
        Request $request,
    ): Response {
        $data = $this->getEmployeeRolesService->getRoles($requestDto);

        return $this->createSuccessResponse(
            $data,
            $data['totalCount']
        );
    }
}
