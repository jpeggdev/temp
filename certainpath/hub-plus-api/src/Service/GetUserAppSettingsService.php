<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\LoggedInUserDTO;
use App\DTO\Response\UserAppSettingsResponseDTO;

readonly class GetUserAppSettingsService
{
    public function __construct(
        private PermissionService $permissionService,
        private SettingManager $settingManager, // ← NEW
    ) {
    }

    public function getUserAppSettings(LoggedInUserDTO $loggedInUserDTO): UserAppSettingsResponseDTO
    {
        $activeCompany = $loggedInUserDTO->getActiveCompany();
        $activeEmployee = $loggedInUserDTO->getActiveEmployee();

        $combinedPermissions = $this->permissionService->getAllPermissionInternalNamesForEmployee($activeEmployee);
        $applications = $this->permissionService->getApplicationAccessForEmployee($activeEmployee);
        $applicationAccess = array_map(fn ($app) => $app->getInternalName(), $applications);

        $legacyBannerToggle = $this->settingManager->getBoolValue('legacyBannerToggle');

        return new UserAppSettingsResponseDTO(
            $loggedInUserDTO->getUser()->getId(),
            $loggedInUserDTO->getUser()->getEmail(),
            $activeEmployee->getFirstName(),
            $activeEmployee->getLastName(),
            $activeEmployee->getUuid(),
            $activeCompany->getCompanyName(),
            $activeCompany->getId(),
            $activeCompany->getIntacctId(),
            $activeEmployee->getRole()?->getInternalName(),
            $combinedPermissions,
            $applicationAccess,
            $activeCompany->isCertainPath(),
            $legacyBannerToggle
        );
    }
}
