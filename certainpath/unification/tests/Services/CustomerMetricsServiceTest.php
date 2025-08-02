<?php

namespace App\Tests\Services;

use App\Entity\Customer;
use App\Entity\Trade;
use App\Message\MigrationMessage;
use App\Parsers\MailManagerLife\MailManagerLifeParser;
use App\Tests\FunctionalTestCase;

class CustomerMetricsServiceTest extends FunctionalTestCase
{
    /**
     * @group largeFiles
     */
    public function testUpdateCustomerMetrics(): void
    {
        $projectDir = self::$kernel->getProjectDir();
        $tmpDir = $projectDir . '/var/tmp';
        $fileToDelete = $tmpDir . '/exports/5a8224c1522d8dac6076cbc421fb2d871a5d8c3f.csv';
        if (file_exists($fileToDelete)) {
            unlink($fileToDelete);
        }
        $recordLimit = 500;//50000;
        $identifier = 'SM000205';
        $filePath = __DIR__ . '/../Files/SM000205/Life-Standard Birmingham AL PLBG Post Oct 2024.DBF';
        $handler = $this->getMigrationHandler();
        self::assertNotNull($handler);
        $handler->__invoke(
            new MigrationMessage(
                $identifier,
                $filePath,
                MailManagerLifeParser::getSourceName(),
                null,
                Trade::electrical()->getName(),
                [],
                $recordLimit
            )
        );
        $company = $this->getCompanyRepository()->findOneByIdentifier($identifier);
        $customers = $this->getCustomerRepository()->findForCompany(
            $company
        );
        self::assertCount(500, $customers);
        foreach ($customers as $customer) {
            $nominalInvoiceCount = $customer->getInvoices()->count();
            $assignedCountInvoices = $customer->getCountInvoices();
            self::assertSame(
                $nominalInvoiceCount,
                $assignedCountInvoices
            );
        }

        $metricsService = $this->getCustomerMetricsService();
        $metricsService->updateCustomerMetricsForCompany($company);
        $customers = $this->getCustomerRepository()->findForCompany(
            $company
        );
        $countHighValueInvoices = 0;
        foreach ($customers as $customer) {
            $nominalInvoiceCount = $customer->getInvoices()->count();
            $assignedCountInvoices = $customer->getCountInvoices();
            self::assertSame(
                $nominalInvoiceCount,
                $assignedCountInvoices
            );
            foreach ($customer->getInvoices() as $invoice) {
                $total = (float) $invoice->getTotal();
                if ($total >= Customer::INSTALLATION_THRESHOLD_FOR_INVOICE) {
                    ++$countHighValueInvoices;
                    self::assertTrue(
                        $customer->hasInstallation(),
                        'Failing Invoice Total: ' . $total
                    );
                }
            }
        }
        self::assertSame(
            14, //938,
            $countHighValueInvoices
        );
    }
}
