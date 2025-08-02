<?php

namespace App\Tests\Commands;

use App\Commands\SingleMigrationCommand;
use App\Entity\Customer;
use App\Entity\Invoice;
use App\Entity\Prospect;
use App\Tests\FunctionalTestCase;
use DateTimeInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class SingleMigrationCommandExcelFileTest extends FunctionalTestCase
{
    public function testSingleMigrationCommand(): void
    {
        $command = $this->getSingleMigrationCommand();
        self::assertNotNull($command);
        $tester = new CommandTester(
            $command
        );
        self::assertNotNull($tester);

        $filePath = __DIR__ . '/../Files/Legacy_Homes_Invoice_Stochastic_Data_ServiceTitan.xlsx';
        $result = $tester->execute([
            'company' => 'ELEC1',
            'filePath' => $filePath,
            '--limit' => 10,
            '--dataSource' => 'servicetitan',
            '--dataType' => null,
        ]);
        $this->assertEquals(Command::SUCCESS, $result);

        // Run this a second time to test for duplication.
        $result = $tester->execute([
            'company' => 'ELEC1',
            'filePath' => $filePath,
            '--limit' => 10,
            '--dataSource' => 'servicetitan',
            '--dataType' => null,
        ]);

        $this->assertEquals(Command::SUCCESS, $result);
        $this->checkProspects();
        $this->checkCustomers();
        $this->checkAddresses();
        $this->checkInvoices();
    }

    protected function getSingleMigrationCommand(): SingleMigrationCommand
    {
        return $this->getService(
            SingleMigrationCommand::class
        );
    }

    private function checkAddresses(): void
    {
        $addresses = $this->getAddressRepository()->findBy([]);
        $addressCount = count($addresses);

        $expectedAddressExtIds = [
            '4215mainetrailcrystallakeil60012',
            '778surryseroadlakezurichil60047',
            '6605hunterspathcaryil60013',
            '2602cobblestonedrivecrystallakeil60012',
            '13121eakincreekcourthuntleyil60142',
            '2603killarneydrivecaryil60013',
            '3010jonathonlanewoodstockil60098',
            '1081cedarcrestdrivecrystallakeil60014',
            '4813mcmillanlanecrystallakeil60012',
        ];
        $addressExtIds = array_map(static function ($address) {
            return $address->getExternalId();
        }, $addresses);

        // Checking for 9 and not 10 because this dataset contains a duplicate Prospect.
        $this->assertEquals(9, $addressCount);
        $this->assertSame($expectedAddressExtIds, $addressExtIds);

        foreach ($addresses as $address) {
            $this->assertFalse($address->isVerified());
        }
    }

    private function checkCustomers(): void
    {
        $customers = $this->getCustomerRepository()->findBy(
            [],
            [
                'id' => 'ASC',
            ]
        );
        $customersCount = count($customers);

        $newCustomers = $this->getCustomerRepository()->findBy([
            'isNewCustomer' => true,
        ]);
        $repeatCustomers = $this->getCustomerRepository()->findBy([
            'isRepeatCustomer' => true,
        ]);

        $customerNames = array_map(static function ($customer) {
            return $customer->getName();
        }, $customers);
        $expectedCustomerNames = [
            'Clement, Todd',
            'Klopfleisch, Dave and Sonya',
            'Smith, Matt',
            'Betzwiser, Matt',
            'Farrant, Scott',
            'Valenti, Cheryl and Pete',
            'Vanderwiel, Jay and Sarah', //This is the duplicate
            'Duignan, John and Linda',
            'Janiga, John and Angie',
        ];

        $expectedCountInvoices = [
            1,
            1,
            1,
            1,
            1,
            1,
            2, // Two entries for this customer.
            1,
            1,
        ];
        $countInvoices = array_map(static function (Customer $customer) {
            return $customer->getCountInvoices();
        }, $customers);

        $expectedInvoiceTotals = [
            '0.00',
            '0.00',
            '0.00',
            '40.00',
            '0.00',
            '0.00',
            '0.00',
            '87.99',
            '0.00',
        ];
        $invoiceTotals = array_map(static function (Customer $customer) {
            return $customer->getInvoiceTotal();
        }, $customers);

        // Checking for 9 and not 10 because this dataset contains a duplicate Prospect.
        $this->assertEquals(9, $customersCount);

        foreach ($customers as $customer) {
            $this->assertNotNull($customer->getName());
            $this->assertNotNull($customer->getInvoiceTotal());
        }

        $this->assertSame($expectedCustomerNames, $customerNames);
        $this->assertCount(0, $newCustomers);
        $this->assertCount(1, $repeatCustomers);
        $this->assertSame($expectedCountInvoices, $countInvoices);
        $this->assertSame($expectedInvoiceTotals, $invoiceTotals);
    }

    private function checkProspects(): void
    {
        $prospects = $this->getProspectRepository()->findBy([], ['externalId' => 'ASC']);
        $prospectsCount = count($prospects);

        $expectedProspectExtIds = [
            'id.betzwisermatt2602cobblestonedrivecrystallakeil60012',
            'id.clementtodd4215mainetrailcrystallakeil60012',
            'id.duignanjohnandlinda1081cedarcrestdrivecrystallakeil60014',
            'id.farrantscott13121eakincreekcourthuntleyil60142',
            'id.janigajohnandangie4813mcmillanlanecrystallakeil60012',
            'id.klopfleischdaveandsonya778surryseroadlakezurichil60047',
            'id.smithmatt6605hunterspathcaryil60013',
            'id.valenticherylandpete2603killarneydrivecaryil60013',
            'id.vanderwieljayandsarah3010jonathonlanewoodstockil60098',
        ];
        $prospectExtIds = array_map(static function (Prospect $prospect) {
            return $prospect->getExternalId();
        }, $prospects);

        // Checking for 9 and not 10 because this dataset contains a duplicate Prospect.
        $this->assertEquals(9, $prospectsCount);
        $this->assertSame($expectedProspectExtIds, $prospectExtIds);

        foreach ($prospects as $prospect) {
            $this->assertInstanceOf(Customer::class, $prospect->getCustomer());
        }
    }

    private function checkInvoices(): void
    {
        $invoices = $this->getInvoiceRepository()->findBy([]);
        $invoicesCount = $this->getInvoiceRepository()->count();

        $invoiceExtIds = array_map(static function ($invoice) {
            return $invoice->getExternalId();
        }, $invoices);
        $expectedInvoiceExtIds = [
            'id.clementtodd4215mainetrailcrystallakeil6001210070170005e7439d77829d2d3b5363ee6cc3ee73f20231030',
            'id.klopfleischdaveandsonya778surryseroadlakezurichil6004710070410008724054b2d8900f7b20d7636999e97c320231030',
            'id.smithmatt6605hunterspathcaryil600131007439000219029cbc8c2a13e8e69dd5a21545d3e20231030',
            'id.betzwisermatt2602cobblestonedrivecrystallakeil60012101733340002c5f3d55d344cb9abe6ae8a78a622aa220231030',
            'id.farrantscott13121eakincreekcourthuntleyil601421017868000d5da7907e3ef0112be339c9905ae1c6220231030',
            'id.valenticherylandpete2603killarneydrivecaryil6001310178930008cfaffd8bbae37a1d44211817813975820231030',
            'id.vanderwieljayandsarah3010jonathonlanewoodstockil600981018020000ae24ee6763da90754f95f19d0d0bc98120231106',
            'id.vanderwieljayandsarah3010jonathonlanewoodstockil600981026301000af47795d6108f98a34a17365e12d99e220231121',
            'id.duignanjohnandlinda1081cedarcrestdrivecrystallakeil60014102745787998d8de9a3b78906a140715b0740c6cb7120231113',
            'id.janigajohnandangie4813mcmillanlanecrystallakeil6001210274760004450c75d272b3bc75ee864ff5886bfd320231113',
        ];

        $expectedInvoiceNumbers = [
            '1007017',
            '1007041',
            '1007439',
            '1017333',
            '1017868',
            '1017893',
            '1018020',
            '1026301',
            '1027457',
            '1027476',
        ];
        $invoiceNumbers = array_map(static function ($invoice) {
            return $invoice->getInvoiceNumber();
        }, $invoices);

        sort($expectedInvoiceExtIds);
        sort($invoiceExtIds);
        sort($expectedInvoiceNumbers);
        sort($invoiceNumbers);

        $this->assertEquals(10, $invoicesCount);
        $this->assertSame(($expectedInvoiceExtIds), $invoiceExtIds);
        $this->assertSame($expectedInvoiceNumbers, $invoiceNumbers);
    }
}
