<?php

namespace App\Tests\Repository;

use App\Entity\Company;
use App\Entity\Report;
use App\Tests\FunctionalTestCase;

class ReportRepositoryTest extends FunctionalTestCase
{
    public function testGetLastReportForCompany(): void
    {
        $company = $this->getCompany();
        $this->getCompanyRepository()->save($company);
        $savedReport = $this->getReport()
            ->setCompany($company);
        $this->getReportRepository()->saveReport($savedReport);

        $report = $this->getReportRepository()->getLastDMERReportForCompany($company);

        $this->assertInstanceOf(Report::class, $report);
        $this->assertSame('dmer', $report->getName());
        $this->assertSame($company, $report->getCompany());
    }
    public function testGetCompaniesNeedingDMERReport(): void
    {
        $company1 = $this->getCompany();
        $this->getCompanyRepository()->save($company1);
        $savedReport1 = $this->getReport()
            ->setCompany($company1);
        $this->getReportRepository()->saveReport($savedReport1);

        $company2 = $this->getCompanyRepository()->findOneBy([
            'identifier' => 'CPA1'
        ]);
        $savedReport2 = $this->getReport()
            ->setCompany($company2)
            ->setLastRun(date_create_immutable('now -3 months'));
        $this->getReportRepository()->saveReport($savedReport2);

        $company3 = $this->getCompanyRepository()->findOneBy([
            'identifier' => 'COM1'
        ]);

        $result = $this->getCompanyRepository()->getCompaniesNeedingDMERUpdate();

        $this->assertFalse($result->contains($company1));
        $this->assertTrue($result->contains($company2));
        $this->assertTrue($result->contains($company3));
    }

    private function getCompany(): Company
    {
        return $this->getCompanyRepository()->findOneBy([
            'identifier' => 'UNI1'
        ]);
    }

    private function getReport(): Report
    {
        return (new Report())
            ->setName('dmer')
            ->setLastRun(date_create_immutable());
    }
}
