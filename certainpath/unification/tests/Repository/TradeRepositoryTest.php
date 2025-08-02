<?php

namespace App\Tests\Repository;

use App\Entity\Company;
use App\Entity\Customer;
use App\Entity\Invoice;
use App\Entity\Trade;
use App\Tests\FunctionalTestCase;

class TradeRepositoryTest extends FunctionalTestCase
{
    public function testTradeHandling(): void
    {
        $repo = $this->getTradeRepository();
        $trades = $repo->getAllTrades();
        self::assertCount(4, $trades);
    }

    public function testInvoiceTrade(): void
    {
        $electrical = $this->getTradeRepository()->get(
            Trade::electrical()
        );
        self::assertTrue(
            $electrical->equals(Trade::electrical())
        );
        $company = new Company();
        $company->setIdentifier('UNI123');
        $company->setName('Uni123');
        $this->getCompanyRepository()->saveCompany($company);
        $customer = new Customer();
        $customer->setName('John Doe');
        $customer->setCompany($company);
        $this->getCustomerRepository()->saveCustomer($customer);
        $invoice = new Invoice();
        $invoice->setCustomer($customer);
        $invoice->setCompany($company);
        $invoice->setTrade($electrical);
        self::assertTrue(
            $invoice->getTrade()->equals(Trade::electrical())
        );
        $this->getInvoiceRepository()->saveInvoice(
            $invoice
        );
    }
}
