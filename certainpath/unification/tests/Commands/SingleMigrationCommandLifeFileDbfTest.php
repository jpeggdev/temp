<?php

namespace App\Tests\Commands;

use App\Commands\SingleMigrationCommand;
use App\Entity\Customer;
use App\Entity\Prospect;
use App\Tests\FunctionalTestCase;
use DateTimeInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class SingleMigrationCommandLifeFileDbfTest extends FunctionalTestCase
{
    public function testSingleMigrationCommand(): void
    {
        $command = $this->getSingleMigrationCommand();
        self::assertNotNull($command);
        $tester = new CommandTester(
            $command
        );
        self::assertNotNull($tester);

        $filePath = __DIR__ . '/../Files/life_sample.dbf';
        $result = $tester->execute([
            'company' => 'UNI1',
            'filePath' => $filePath,
            '--limit' => 10,
        ]);

        $this->assertEquals(Command::SUCCESS, $result);
        $this->checkProspects();
        $this->checkCustomers();
        $this->checkAddresses();
        $this->checkInvoices();
    }

    protected function getSingleMigrationCommand(): SingleMigrationCommand
    {
        return $this->getService(SingleMigrationCommand::class);
    }

    private function checkAddresses(): void
    {
        $addresses = $this->getAddressRepository()->findBy([]);
        $addressesCount = $this->getAddressRepository()->count();
        $expectedAddressExtIds = [
            '9539elindermesaaz85209',
            '16403wlancectsurpriseaz85387',
            '9448eplataavemesaaz85212',
            '5450emclellanrdunit117mesaaz85205',
            '3300ebroadwayrdlot51mesaaz85204',
            '640wmedinaavemesaaz85210',
            '2400ebaselineavelot43apachejunctionaz85119',
            '18048eviajardingoldcanyonaz85118',
            '6214eelpasostmesaaz85205',
            '6202esierramorenastmesaaz85215',
        ];
        $addressExtIds = array_map(static function ($address) {
            return $address->getExternalId();
        }, $addresses);

        $this->assertEquals(10, $addressesCount);
        $this->assertSame($expectedAddressExtIds, $addressExtIds);

        foreach ($addresses as $address) {
            $this->assertInstanceOf(DateTimeInterface::class, $address->getVerifiedAt());
            $this->assertTrue($address->isVerified());
        }
    }

    private function checkCustomers(): void
    {
        $customers = $this->getCustomerRepository()->findBy(
            [],
            ['id' => 'ASC']
        );
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
            'Bill Grout',
            'Schultz, Terry',
            'Matt Campbell',
            'Jim Lewis',
            'Dave Reurink',
            'Anders, Morgan',
            'Roger Bittner',
            'Mr. & Mrs. Davis',
            'Gary Lewis',
            'Raulf Annicchiarico',
        ];

        $expectedCountInvoices = [
            1,
            1,
            1,
            1,
            1,
            1,
            1,
            1,
            1,
            1,
        ];

        $expectedInvoiceTotals = [
            '3000.00',
            '17.75',
            '6000.00',
            '5000.00',
            '5500.00',
            '100.00',
            '5500.00',
            '6500.00',
            '287.00',
            '5000.00',
        ];

        $countInvoices = array_map(static function (Customer $customer) {
            return $customer->getCountInvoices();
        }, $customers);
        $this->assertSame($expectedCountInvoices, $countInvoices);

        $invoiceTotals = array_map(static function (Customer $customer) {
            return $customer->getInvoiceTotal();
        }, $customers);

        $this->assertEquals(10, $this->getCustomerRepository()->count());

        foreach ($customers as $customer) {
            $this->assertNotNull($customer->getName());
            $this->assertNotNull($customer->getInvoiceTotal());
        }

        $this->assertSame($expectedCustomerNames, $customerNames);
        $this->assertCount(0, $newCustomers);

        //this should now be zero
        //because we are no-longer factoring legacyCountInvoices
        //in the invoice count.
        //Because we now import individual invoice records
        $this->assertCount(0, $repeatCustomers);


        $this->assertSame($expectedInvoiceTotals, $invoiceTotals);
    }

    private function checkProspects(): void
    {
        $prospects = $this->getProspectRepository()->findBy([], ['externalId' => 'ASC']);
        $prospectExtIds = array_map(static function (Prospect $prospect) {
            return $prospect->getExternalId();
        }, $prospects);
        $expectedProspectExtIds = [
            'id.andersmorgan640wmedinaavemesaaz85210',
            'id.billgrout9539elindermesaaz85209',
            'id.davereurink3300ebroadwayrdlot51mesaaz85204',
            'id.garylewis6214eelpasostmesaaz85205',
            'id.jimlewis5450emclellanrdunit117mesaaz85205',
            'id.mattcampbell9448eplataavemesaaz85212',
            'id.mrmrsdavis18048eviajardingoldcanyonaz85118',
            'id.raulfannicchiarico6202esierramorenastmesaaz85215',
            'id.rogerbittner2400ebaselineavelot43apachejunctionaz85119',
            'id.schultzterry16403wlancectsurpriseaz85387',
        ];

        $this->assertEquals(10, $this->getProspectRepository()->count());
        $this->assertSame($expectedProspectExtIds, $prospectExtIds);

        foreach ($prospects as $prospect) {
            $this->assertInstanceOf(Customer::class, $prospect->getCustomer());
        }
    }

    private function checkInvoices(): void
    {
        $invoices = $this->getInvoiceRepository()->findBy([]);
        $expectedInvoiceExtIds = [
            'id.billgrout9539elindermesaaz85209300000466eadd40b3c10580e3ab4e8061161ce20170101',
            'id.schultzterry16403wlancectsurpriseaz853871775466eadd40b3c10580e3ab4e8061161ce20170101',
            'id.mattcampbell9448eplataavemesaaz85212600000466eadd40b3c10580e3ab4e8061161ce20170102',
            'id.jimlewis5450emclellanrdunit117mesaaz85205500000466eadd40b3c10580e3ab4e8061161ce20170103',
            'id.davereurink3300ebroadwayrdlot51mesaaz85204550000466eadd40b3c10580e3ab4e8061161ce20170110',
            'id.andersmorgan640wmedinaavemesaaz8521010000466eadd40b3c10580e3ab4e8061161ce20170112',
            'id.rogerbittner2400ebaselineavelot43apachejunctionaz85119550000466eadd40b3c10580e3ab4e8061161ce20170123',
            'id.mrmrsdavis18048eviajardingoldcanyonaz85118650000466eadd40b3c10580e3ab4e8061161ce20170124',
            'id.garylewis6214eelpasostmesaaz8520528700466eadd40b3c10580e3ab4e8061161ce20170126',
            'id.raulfannicchiarico6202esierramorenastmesaaz85215500000466eadd40b3c10580e3ab4e8061161ce20170126',
        ];
        $invoiceExtIds = array_map(static function ($invoice) {
            return $invoice->getExternalId();
        }, $invoices);

        sort($expectedInvoiceExtIds);
        sort($invoiceExtIds);

        $this->assertEquals(10, $this->getInvoiceRepository()->count());
        $this->assertSame($expectedInvoiceExtIds, $invoiceExtIds);
    }
}
