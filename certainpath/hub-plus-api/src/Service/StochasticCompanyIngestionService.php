<?php

namespace App\Service;

use App\Entity\Company;
use App\Repository\CompanyRepository;
use OpenSpout\Common\Exception\IOException;
use OpenSpout\Reader\Exception\ReaderNotOpenedException;

readonly class StochasticCompanyIngestionService
{
    public function __construct(
        private StochasticRosterLoaderService $rosterLoaderService,
        private CompanyRepository $companyRepository,
    ) {
    }

    /**
     * @throws IOException
     * @throws ReaderNotOpenedException
     * @throws \DateMalformedStringException
     */
    public function updateAllCompaniesFromStochasticRoster(): void
    {
        $companies = $this->rosterLoaderService->getRoster();
        foreach ($companies as $company) {
            $this->companyRepository->ingestCompanyFromStochasticRoster(
                $company
            );
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
