<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\LoggedInUserDTO;
use App\DTO\Request\Company\UpdateMyCompanyProfileRequestDTO;
use App\Service\Company\UpdateMyCompanyProfileService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class UpdateMyCompanyProfileController extends ApiController
{
    public function __construct(
        private readonly UpdateMyCompanyProfileService $updateMyCompanyProfileService,
    ) {
    }

    #[Route('/my-company-profile', name: 'api_company_update_my_profile', methods: ['PUT'])]
    public function __invoke(
        #[MapRequestPayload] UpdateMyCompanyProfileRequestDTO $updateRequestDTO,
        LoggedInUserDTO $loggedInUserDTO,
    ): Response {
        $this->updateMyCompanyProfileService->updateMyCompanyProfile($loggedInUserDTO, $updateRequestDTO);

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
