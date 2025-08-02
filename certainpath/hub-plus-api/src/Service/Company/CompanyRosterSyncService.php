<?php

namespace App\Service\Company;

use App\Entity\Company;
use App\Service\ApplicationSignalingService;
use App\Service\SalesforceRosterService;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

readonly class CompanyRosterSyncService
{
    public function __construct(
        private SalesforceRosterService $salesforceRosterService,
        private CompanyRosterService $companyRosterService,
        private ApplicationSignalingService $signal,
    ) {
    }

    public function setSignaling(OutputInterface $output): void
    {
        $this->signal->setOutput(
            $output
        );
        $this->companyRosterService->setSignaling(
            $output
        );
        $this->salesforceRosterService->setSignaling(
            $output
        );
    }

    /**
     * @return Company[]
     *
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws \Exception
     */
    public function syncAllCompanies(?int $limit = null): array
    {
        $this->signal->console(
            'Syncing companies',
        );

        return $this
            ->companyRosterService
            ->createOrUpdateCompaniesRosters(
                $this
                    ->salesforceRosterService
                    ->getCompanies($limit)
            );
    }

    /**
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws \Exception
     */
    public function syncSingleCompanyByIntacctId(string $intacctId): Company
    {
        $salesforceCompany = $this->salesforceRosterService->getCompanyByIntacctId(
            $intacctId
        );

        return $this->companyRosterService->createOrUpdateCompanyRoster(
            $salesforceCompany
        );
    }
}
