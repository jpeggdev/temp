<?php

declare(strict_types=1);

namespace App\Service\Company;

use App\DTO\Request\Company\EditCompanyDTO;
use App\DTO\Response\Company\EditCompanyResponseDTO;
use App\Entity\Company;
use App\Repository\CompanyRepository;

readonly class EditCompanyService
{
    public function __construct(
        private CompanyRepository $companyRepository,
    ) {
    }

    public function editCompany(Company $company, EditCompanyDTO $editCompanyDTO): EditCompanyResponseDTO
    {
        $company->setCompanyName($editCompanyDTO->companyName);
        $company->setSalesforceId($editCompanyDTO->salesforceId);
        $company->setIntacctId($editCompanyDTO->intacctId);
        $company->setCompanyEmail($editCompanyDTO->companyEmail);
        $company->setWebsiteUrl($editCompanyDTO->websiteUrl);
        $company->setMarketingEnabled($editCompanyDTO->marketingEnabled);

        $this->companyRepository->save($company, true);

        return EditCompanyResponseDTO::fromEntity($company);
    }
}
