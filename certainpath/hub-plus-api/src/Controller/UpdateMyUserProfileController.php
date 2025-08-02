<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\LoggedInUserDTO;
use App\DTO\Request\UpdateMyUserProfileRequestDTO;
use App\Service\UpdateMyUserProfileService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class UpdateMyUserProfileController extends ApiController
{
    public function __construct(
        private readonly UpdateMyUserProfileService $updateMyUserProfileService,
    ) {
    }

    #[Route('/my-user-profile', name: 'api_user_update_my_profile', methods: ['PUT'])]
    public function __invoke(
        #[MapRequestPayload] UpdateMyUserProfileRequestDTO $updateMyUserProfileRequestDTO,
        LoggedInUserDTO $loggedInUserDTO,
    ): Response {
        $this->updateMyUserProfileService->updateMyUserProfile($loggedInUserDTO, $updateMyUserProfileRequestDTO);

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
