<?php

namespace App\Tests\Services;

use App\DTO\Domain\StatusDTO;
use App\Tests\FunctionalTestCase;
use Throwable;

class CompanyStatusServiceTest extends FunctionalTestCase
{
    /**
     * @throws Throwable
     */
    public function testCompanyStatus(): void
    {
        $tenantIdentifier = 'UNI1';
        $this->initializeEventStatuses();
        $jobService = $this->getCompanyJobService();
        $tenantStream = $this->getTenantStreamAuditService();
        self::assertNotNull($tenantStream);
        $company = $this->initializeCompany(
            $tenantIdentifier
        );
        $jobService->startJobForCompany(
            $company,
            'test_job'
        );
        $jobService->startJobForCompany(
            $company,
            'test_job_2'
        );
        $prospectsCount = $tenantStream->getProspectsCount(
            $tenantIdentifier
        );
        $membersCount = $tenantStream->getMembersCount(
            $tenantIdentifier
        );
        $invoicesCount = $tenantStream->getInvoiceCount(
            $tenantIdentifier
        );
        self::assertSame(
            5,
            $prospectsCount
        );
        self::assertSame(
            0,
            $membersCount
        );
        self::assertSame(
            3,
            $invoicesCount
        );
        $jobsCount = $jobService->getCompanyActiveJobEventCount(
            $company
        );
        self::assertSame(
            2,
            $jobsCount
        );
        $companyStatusService = $this->getCompanyStatusService();
        self::assertNotNull($companyStatusService);

        $someDTO = new StatusDTO(
            [
                'Jobs Running' => 0,
                'Prospects to Process' => $prospectsCount,
                'Customers to Process' => $membersCount,
                'Invoices to Process' => $invoicesCount,
            ]
        );
        $arrayOne = [
            'Jobs Running' => 2,
            'Prospects to Process' => $prospectsCount,
            'Customers to Process' => $membersCount,
            'Invoices to Process' => $invoicesCount,
        ];
        $copyOfArrayOne = [
            'Jobs Running' => 2,
            'Prospects to Process' => $prospectsCount,
            'Customers to Process' => $membersCount,
            'Invoices to Process' => $invoicesCount,
        ];
        self::assertSame(
            $arrayOne,
            $copyOfArrayOne
        );
        $referenceStatusDTO = new StatusDTO(
            $arrayOne
        );
        $copyOfReferenceStatusDTO = new StatusDTO(
            $copyOfArrayOne
        );
        self::assertFalse(
            $someDTO->equals($referenceStatusDTO)
        );
        self::assertTrue(
            $referenceStatusDTO->equals($copyOfReferenceStatusDTO)
        );

        $companyStatus = $companyStatusService->getCompanyStatus(
            $company
        );

        self::assertTrue(
            $companyStatus->equals($referenceStatusDTO)
        );
    }
}
