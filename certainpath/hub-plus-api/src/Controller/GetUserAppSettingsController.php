<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\LoggedInUserDTO;
use App\Service\GetUserAppSettingsService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class GetUserAppSettingsController extends ApiController
{
    private GetUserAppSettingsService $getUserAppSettingsService;

    public function __construct(GetUserAppSettingsService $getUserAppSettingsService)
    {
        $this->getUserAppSettingsService = $getUserAppSettingsService;
    }

    #[Route('/user-app-settings', name: 'api_user_app_settings_get', methods: ['GET'])]
    public function __invoke(LoggedInUserDTO $loggedInUserDTO): Response
    {
        $userAppSettingsDto = $this->getUserAppSettingsService->getUserAppSettings($loggedInUserDTO);

        return $this->createSuccessResponse($userAppSettingsDto);
    }
}
