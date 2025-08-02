<?php

declare(strict_types=1);

namespace App\Service\Company;

use App\DTO\LoggedInUserDTO;
use App\DTO\Response\GetMyCompanyProfileResponseDTO;

class GetMyCompanyProfileService
{
    public function getMyCompanyProfile(LoggedInUserDTO $loggedInUserDTO): GetMyCompanyProfileResponseDTO
    {
        $company = $loggedInUserDTO->getActiveCompany();

        return new GetMyCompanyProfileResponseDTO(
            companyName: $company->getCompanyName(),
            companyEmail: $company->getCompanyEmail(),
            websiteUrl: $company->getWebsiteUrl(),
            addressLine1: $company->getAddressLine1(),
            addressLine2: $company->getAddressLine2(),
            city: $company->getCity(),
            state: $company->getState(),
            country: $company->getCountry(),
            zipCode: $company->getZipCode(),
            isMailingAddressSame: $company->isMailingAddressSame(),
            mailingAddressLine1: $company->getMailingAddressLine1(),
            mailingAddressLine2: $company->getMailingAddressLine2(),
            mailingState: $company->getMailingState(),
            mailingCountry: $company->getMailingCountry(),
            mailingZipCode: $company->getMailingZipCode(),
            uuid: $company->getUuid(),
        );
    }
}
