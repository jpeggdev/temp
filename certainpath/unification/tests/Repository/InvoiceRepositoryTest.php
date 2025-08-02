<?php

namespace App\Tests\Repository;

use App\DTO\Query\Invoice\DailySalesQueryDTO;
use App\Entity\Company;
use App\Entity\Customer;
use App\Entity\Invoice;
use App\Entity\Prospect;
use App\Tests\FunctionalTestCase;
use App\ValueObjects\InvoiceObject;
use App\ValueObjects\ProspectObject;
use Doctrine\DBAL\Exception;

class InvoiceRepositoryTest extends FunctionalTestCase
{
    private ProspectObject $prospectObject;
    private Company $company;
    private Prospect $prospect;
    private Customer $customer;
    private InvoiceObject $invoiceObject1;
    private InvoiceObject $invoiceObject2;

    /**
     * @throws Exception
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->company = $this->initializeCompany();

        $this->prospectObject = new ProspectObject([
            'fullName' => 'fullName',
            'address1' => 'address1',
            'address2' => 'address2',
            'city' => 'New York',
            'state' => 'NY',
            'postalCode' => '00001',
        ]);
        $this->prospectObject->externalId = $this->prospectObject->getKey();

        $this->prospect = $this->getProspectRepository()->saveProspect(
            (new Prospect())
                ->fromValueObject($this->prospectObject)
                ->setCompany($this->company)
        );

        $this->customer = $this->getCustomerRepository()->saveCustomer(
            (new Customer())
                ->setName('ANAND, ABHILASH')
                ->setCompany($this->company)
                ->setProspect($this->prospect)
        );

        $this->invoiceObject1 = new InvoiceObject([
            'prospect' => $this->prospectObject,
            'total' => '100.00',
            'description' => 'Test Invoice',
            'invoicedAt' => date_create_immutable('2024-01-01'),
            'companyId' => $this->company->getId(),
        ]);
        $this->invoiceObject1->externalId = $this->invoiceObject1->getKey();

        $this->getInvoiceRepository()->saveInvoice(
            (new Invoice())
                ->fromValueObject($this->invoiceObject1)
                ->setCompany($this->company)
                ->setCustomer($this->customer)
        );

        $this->invoiceObject2 = new InvoiceObject([
            'prospect' => $this->prospectObject,
            'total' => '100.00',
            'invoiceNumber' => 'INV-001',
            'description' => 'Test Invoice',
            'invoicedAt' => date_create_immutable('2024-01-01'),
            'companyId' => $this->company->getId(),
        ]);
        $this->invoiceObject2->externalId = $this->invoiceObject2->getKey();

        $this->getInvoiceRepository()->saveInvoice(
            (new Invoice())
                ->fromValueObject($this->invoiceObject2)
                ->setCompany($this->company)
                ->setCustomer($this->customer)
        );
    }

    public function testResolveInvoice(): void
    {


        $invoice = $this->getInvoiceRepository()->resolveInvoice(
            $this->company,
            $this->prospect,
            $this->invoiceObject1
        );

        $this->assertNotNull($invoice->getId());
    }

    public function testResolveToExpectedInvoice(): void
    {
        $invoice = $this->getInvoiceRepository()->resolveInvoice(
            $this->company,
            $this->prospect,
            $this->invoiceObject1
        );

        $this->assertNotNull($invoice->getId());
        $this->assertSame(
            'fullnameaddress1address2newyorkny00001100005f8de45d53eacdc83e3c6f2db54d10b920240101',
            $invoice->getExternalId()
        );

        $invoice = $this->getInvoiceRepository()->resolveInvoice(
            $this->company,
            $this->prospect,
            $this->invoiceObject2
        );

        $this->assertNotNull($invoice->getId());
        $this->assertSame(
            'fullnameaddress1address2newyorkny00001inv001100005f8de45d53eacdc83e3c6f2db54d10b920240101',
            $invoice->getExternalId()
        );

        $this->invoiceObject1->externalId = null;
        $this->invoiceObject1->invoiceNumber = 'INV-001';
        $this->invoiceObject1->populate();
        $invoice = $this->getInvoiceRepository()->resolveInvoice(
            $this->company,
            $this->prospect,
            $this->invoiceObject1
        );
        $this->assertSame(
            'fullnameaddress1address2newyorkny00001inv001100005f8de45d53eacdc83e3c6f2db54d10b920240101',
            $invoice->getExternalId()
        );
    }

    public function testGetDMERDailySalesDTOCollection(): void
    {
        $dailySalesQueryDTO = new DailySalesQueryDTO(
            'test-company',
            date_create_immutable('2024-01-01'),
            date_create_immutable('2024-01-31'),
        );

        $result = $this->getInvoiceRepository()->getDMRDailySalesData($dailySalesQueryDTO);

        $this->assertCount(30, $result);
        $this->assertSame('2024-01-01', $result->first()->date->format('Y-m-d'));
        $this->assertSame('0', $result->first()->totalCalls);
        $this->assertSame('2', $result->first()->totalSales);
        $this->assertSame('200.00', $result->first()->totalSalesAmount);
    }
}
