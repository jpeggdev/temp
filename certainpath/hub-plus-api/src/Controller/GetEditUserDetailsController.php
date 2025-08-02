<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\LoggedInUserDTO;
use App\Entity\Employee;
use App\Security\Voter\EmployeeVoter;
use App\Service\GetEditUserDetailsService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class GetEditUserDetailsController extends ApiController
{
    public function __construct(private readonly GetEditUserDetailsService $getEditUserDetailsService)
    {
    }

    #[Route('/edit-user-details/{uuid}', name: 'api_user_get_edit_details', methods: ['GET'])]
    public function __invoke(Employee $employee, LoggedInUserDTO $loggedInUserDTO): Response
    {
        $this->denyAccessUnlessGranted(EmployeeVoter::EDIT, $employee);
        $userData = $this->getEditUserDetailsService->getEditUserDetails($employee);

        return $this->createSuccessResponse($userData);
    }
}
