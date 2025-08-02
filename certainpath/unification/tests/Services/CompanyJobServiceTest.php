<?php

namespace App\Tests\Services;

use App\Tests\FunctionalTestCase;

class CompanyJobServiceTest extends FunctionalTestCase
{
    /**
     * @throws \Throwable
     */
    public function testCompanyJobProgress(): void
    {
        $this->initializeEventStatuses();
        $jobService = $this->getCompanyJobService();
        self::assertNotNull($jobService);

        $company = $this->initializeCompany(
            'SM000250'
        );

        self::assertNotNull($company);

        $jobService->startJobForCompany(
            $company,
            'test_job'
        );
        self::assertTrue(
            $jobService->isJobInProgressForCompany(
                $company,
                'test_job'
            )
        );
        self::assertSame(
            1,
            $jobService->getCompanyActiveJobEventCount(
                $company,
            )
        );
        $jobService->startJobForCompany(
            $company,
            'test_job_2'
        );
        self::assertSame(
            2,
            $jobService->getCompanyActiveJobEventCount(
                $company
            )
        );
        $jobService->completeJobForCompany(
            $company,
            'test_job'
        );
        self::assertFalse(
            $jobService->isJobInProgressForCompany(
                $company,
                'test_job'
            )
        );
        self::assertTrue(
            $jobService->isJobEndedForCompany(
                $company,
                'test_job'
            )
        );
        self::assertSame(
            1,
            $jobService->getCompanyActiveJobEventCount(
                $company
            )
        );
        $jobService->completeJobForCompany(
            $company,
            'test_job_2'
        );
        self::assertSame(
            0,
            $jobService->getCompanyActiveJobEventCount(
                $company
            )
        );
    }
}
