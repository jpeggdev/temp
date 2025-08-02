<?php

declare(strict_types=1);

namespace App\Service\Company;

use App\DTO\LoggedInUserDTO;
use App\DTO\Request\Company\UpdateMyCompanyProfileRequestDTO;
use Doctrine\ORM\EntityManagerInterface;

readonly class UpdateMyCompanyProfileService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function updateMyCompanyProfile(
        LoggedInUserDTO $loggedInUserDTO,
        UpdateMyCompanyProfileRequestDTO $updateRequest,
    ): void {
        $company = $loggedInUserDTO->getActiveCompany();

        $company->setCompanyName($updateRequest->companyName);
        $company->setCompanyEmail($updateRequest->companyEmail);
        $company->setWebsiteUrl($updateRequest->websiteUrl);
        $company->setAddressLine1($updateRequest->addressLine1);
        $company->setAddressLine2($updateRequest->addressLine2);
        $company->setCity($updateRequest->city);
        $company->setState($updateRequest->state);
        $company->setCountry($updateRequest->country);
        $company->setZipCode($updateRequest->zipCode);
        $company->setMailingAddressSame($updateRequest->isMailingAddressSame);
        $company->setMailingAddressLine1($updateRequest->mailingAddressLine1);
        $company->setMailingAddressLine2($updateRequest->mailingAddressLine2);
        $company->setMailingState($updateRequest->mailingState);
        $company->setMailingCountry($updateRequest->mailingCountry);
        $company->setMailingZipCode($updateRequest->mailingZipCode);

        $this->entityManager->persist($company);
        $this->entityManager->flush();
    }
}
