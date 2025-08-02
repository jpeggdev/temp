<?php

namespace App\Service;

use App\Entity\Company;
use App\Repository\CompanyRepository;
use App\Repository\External\AccountApplicationRepository;
use Doctrine\DBAL\Exception;

readonly class AccountApplicationCompanyIngestionService
{
    public function __construct(
        private AccountApplicationRepository $accountApplicationRepository,
        private CompanyRepository $companyRepository,
    ) {
    }

    /**
     * @throws \DateMalformedStringException
     * @throws Exception
     */
    public function updateAllCompaniesFromAccountApplication(): void
    {
        $companies = $this->accountApplicationRepository->getAllActiveCompanies();
        foreach ($companies as $company) {
            $this->companyRepository->ingestCompanyFromAccountApplication($company);
        }
    }

    /**
     * @return Company[]
     */
    public function getActiveCompanies(): array
    {
        return $this->companyRepository->findAll();
    }
}
