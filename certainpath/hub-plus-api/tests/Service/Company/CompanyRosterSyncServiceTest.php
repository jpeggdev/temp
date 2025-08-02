<?php

namespace App\Tests\Service\Company;

use App\Entity\Employee;
use App\Tests\AbstractKernelTestCase;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class CompanyRosterSyncServiceTest extends AbstractKernelTestCase
{
    public function setUp(): void
    {
        $this->doInitializeBusinessRoles = true;
        parent::setUp();
    }
    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function testCompanyRosterSync(): void
    {
        $service = $this->getCompanyRosterSyncService();
        self::assertNotNull($service);

        $salesforceCompanies = $this
            ->getSalesforceRosterService()
            ->getCompanies(5);
        $syncedCompanies = $service->syncAllCompanies(5);

        self::assertCount(
            count($salesforceCompanies),
            $syncedCompanies
        );
    }

    /**
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws \JsonException
     */
    public function testSyncSingleCompany(): void
    {
        $service = $this->getCompanyRosterSyncService();
        $company = $service->syncSingleCompanyByIntacctId(
            'ES37623'
        );
        self::assertNotNull($company);
        $this->debug($company->getIntacctId());
        /** @var Employee[] $employees */
        $employees = $this->employeeRepository->findBy(
            [
                'company' => $company,
            ]
        );
        self::assertGreaterThanOrEqual(22, count($employees));
    }
}
