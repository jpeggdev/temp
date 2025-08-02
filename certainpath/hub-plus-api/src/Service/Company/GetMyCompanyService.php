<?php

declare(strict_types=1);

namespace App\Service\Company;

use App\DTO\Response\Company\GetMyCompanyResponseDTO;
use App\Service\GetLoggedInUserDTOService;

class GetMyCompanyService
{
    private GetLoggedInUserDTOService $getLoggedInUserDTOService;

    public function __construct(GetLoggedInUserDTOService $getLoggedInUserDTOService)
    {
        $this->getLoggedInUserDTOService = $getLoggedInUserDTOService;
    }

    public function getMyCompanyDetails(): GetMyCompanyResponseDTO
    {
        $loggedInUserDTO = $this->getLoggedInUserDTOService->getLoggedInUserDTO();
        $company = $loggedInUserDTO->getActiveCompany();

        return GetMyCompanyResponseDTO::fromEntity($company);
    }
}
