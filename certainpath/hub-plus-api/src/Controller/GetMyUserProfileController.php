<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\LoggedInUserDTO;
use App\Service\GetMyUserProfileService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class GetMyUserProfileController extends ApiController
{
    public function __construct(private readonly GetMyUserProfileService $getMyUserProfileService)
    {
    }

    #[Route('/my-user-profile', name: 'api_user_get_my_profile', methods: ['GET'])]
    public function __invoke(LoggedInUserDTO $loggedInUserDTO): Response
    {
        $userData = $this->getMyUserProfileService->getMyUserProfile($loggedInUserDTO);

        return $this->createSuccessResponse($userData);
    }
}
