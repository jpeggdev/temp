<?php

declare(strict_types=1);

namespace App\DTO\Response;

class UserAppSettingsResponseDTO
{
    public function __construct(
        public int $userId,
        public string $email,
        public string $firstName,
        public string $lastName,
        public string $employeeUuid,
        public string $companyName,
        public int $companyId,
        public ?string $intacctId,
        public ?string $roleName,
        public array $permissions,
        public array $applicationAccess,
        public bool $isCertainPathCompany,
        public bool $legacyBannerToggle,
    ) {
    }
}
