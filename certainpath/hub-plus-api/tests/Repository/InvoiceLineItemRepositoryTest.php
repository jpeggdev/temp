<?php

namespace App\Tests\Repository;

use App\Entity\Company;
use App\Entity\Invoice;
use App\Entity\InvoiceLineItem;
use App\Enum\InvoiceStatusType;
use App\Repository\InvoiceLineItemRepository;
use App\Tests\AbstractKernelTestCase;

class InvoiceLineItemRepositoryTest extends AbstractKernelTestCase
{
    private InvoiceLineItemRepository $repository;

    public function setUp(): void
    {
        parent::setUp();
        /** @var InvoiceLineItemRepository $lineItemRepository */
        $lineItemRepository = $this->entityManager->getRepository(InvoiceLineItem::class);
        $this->repository = $lineItemRepository;
    }

    private function createTestCompany(): Company
    {
        $company = new Company();
        $company->setCompanyName($this->faker->company());
        $company->setUuid($this->faker->uuid());
        $this->entityManager->persist($company);

        return $company;
    }

    private function createTestInvoice(Company $company): Invoice
    {
        $invoice = new Invoice();
        $invoice->setInvoiceNumber($this->faker->bothify('INV-####'));
        $invoice->setCompany($company);
        $invoice->setInvoiceDate(new \DateTimeImmutable());
        $invoice->setStatus(InvoiceStatusType::DRAFT);
        $invoice->setTotalAmount('100.00');
        $this->entityManager->persist($invoice);

        return $invoice;
    }

    private function createTestInvoiceLineItem(Invoice $invoice, ?string $discountCode = null): InvoiceLineItem
    {
        $lineItem = new InvoiceLineItem();
        $lineItem->setInvoice($invoice);
        $lineItem->setDescription($this->faker->sentence());
        $lineItem->setQuantity(1);
        $lineItem->setUnitPrice('100.00');
        $lineItem->setLineTotal('100.00');

        if ($discountCode) {
            $lineItem->setDiscountCode($discountCode);
        }

        $this->entityManager->persist($lineItem);

        return $lineItem;
    }

    public function testCountInvoiceLineItemsByDiscountCode(): void
    {
        $company = $this->createTestCompany();
        $invoice1 = $this->createTestInvoice($company);
        $invoice2 = $this->createTestInvoice($company);

        $this->createTestInvoiceLineItem($invoice1, 'SUMMER2025');
        $this->createTestInvoiceLineItem($invoice1, 'SUMMER2025');
        $this->createTestInvoiceLineItem($invoice1, 'WINTER2025');
        $this->createTestInvoiceLineItem($invoice2, 'SUMMER2025');
        $this->createTestInvoiceLineItem($invoice2);

        $this->entityManager->flush();

        $summerCount = $this->repository->countInvoiceLineItemsByDiscountCode('SUMMER2025');
        self::assertSame(3, $summerCount);

        $winterCount = $this->repository->countInvoiceLineItemsByDiscountCode('WINTER2025');
        self::assertSame(1, $winterCount);

        $nonExistentCount = $this->repository->countInvoiceLineItemsByDiscountCode('NONEXISTENT');
        self::assertSame(0, $nonExistentCount);

        $lowercaseCount = $this->repository->countInvoiceLineItemsByDiscountCode('summer2025');
        if ('SUMMER2025' !== strtolower('SUMMER2025')) {
            self::assertSame(0, $lowercaseCount, 'Discount code search should be case-sensitive');
        }
    }
}
