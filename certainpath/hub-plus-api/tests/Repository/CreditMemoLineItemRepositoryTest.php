<?php

namespace App\Tests\Repository;

use App\Entity\Company;
use App\Entity\CreditMemo;
use App\Entity\CreditMemoLineItem;
use App\Entity\Invoice;
use App\Enum\CreditMemoStatusType;
use App\Enum\CreditMemoType;
use App\Enum\InvoiceStatusType;
use App\Repository\CreditMemoLineItemRepository;
use App\Tests\AbstractKernelTestCase;

class CreditMemoLineItemRepositoryTest extends AbstractKernelTestCase
{
    private CreditMemoLineItemRepository $repository;

    public function setUp(): void
    {
        parent::setUp();
        /** @var CreditMemoLineItemRepository $memoLineItemRepository */
        $memoLineItemRepository = $this->entityManager->getRepository(CreditMemoLineItem::class);
        $this->repository = $memoLineItemRepository;
    }

    private function createCompany(): Company
    {
        $company = new Company();
        $company->setCompanyName($this->faker->company());
        $company->setUuid($this->faker->uuid());
        $this->entityManager->persist($company);
        $this->entityManager->flush();

        return $company;
    }

    private function createInvoice(Company $company): Invoice
    {
        $invoice = new Invoice();
        $invoice->setCompany($company);
        $invoice->setUuid($this->faker->uuid());
        $invoice->setInvoiceNumber($this->faker->bothify('INV-#####'));

        $invoice->setInvoiceDate(new \DateTimeImmutable());
        $invoice->setStatus(InvoiceStatusType::DRAFT);
        $invoice->setTotalAmount('100.00');

        $this->entityManager->persist($invoice);
        $this->entityManager->flush();

        return $invoice;
    }

    private function createCreditMemo(
        Invoice $invoice,
        CreditMemoType $type = CreditMemoType::VOUCHER,
    ): CreditMemo {
        $creditMemo = new CreditMemo();
        $creditMemo->setInvoice($invoice);
        $creditMemo->setUuid($this->faker->uuid());
        $creditMemo->setCmDate(new \DateTimeImmutable());
        $creditMemo->setStatus(CreditMemoStatusType::DRAFT);
        $creditMemo->setTotalAmount('100.00');
        $creditMemo->setReason($this->faker->sentence());
        $creditMemo->setType($type);

        $this->entityManager->persist($creditMemo);
        $this->entityManager->flush();

        return $creditMemo;
    }

    private function createCreditMemoLineItem(
        CreditMemo $creditMemo,
        ?string $voucherCode = null,
    ): CreditMemoLineItem {
        $lineItem = new CreditMemoLineItem();
        $lineItem->setCreditMemo($creditMemo);
        $lineItem->setUuid($this->faker->uuid());
        $lineItem->setDescription($this->faker->sentence());
        $lineItem->setAmount('50.00');

        if ($voucherCode) {
            $lineItem->setVoucherCode($voucherCode);
        }

        $this->entityManager->persist($lineItem);
        $this->entityManager->flush();

        return $lineItem;
    }

    public function testCountVoucherLineItemsForCompany(): void
    {
        $company1 = $this->createCompany();
        $company2 = $this->createCompany();

        $invoice1 = $this->createInvoice($company1);
        $invoice2 = $this->createInvoice($company2);

        $creditMemo1 = $this->createCreditMemo($invoice1);

        $this->createCreditMemoLineItem($creditMemo1, 'VOUCHER-001');
        $this->createCreditMemoLineItem($creditMemo1, 'VOUCHER-002');
        $this->createCreditMemoLineItem($creditMemo1, 'VOUCHER-003');

        $creditMemo2 = $this->createCreditMemo($invoice2);

        $this->createCreditMemoLineItem($creditMemo2, 'VOUCHER-004');
        $this->createCreditMemoLineItem($creditMemo2, 'VOUCHER-005');

        $count1 = $this->repository->countVoucherLineItemsForCompany($company1);
        self::assertSame(3, $count1);

        $count2 = $this->repository->countVoucherLineItemsForCompany($company2);
        self::assertSame(2, $count2);

        $company3 = $this->createCompany();
        $count3 = $this->repository->countVoucherLineItemsForCompany($company3);
        self::assertSame(0, $count3);

        $this->createCreditMemoLineItem($creditMemo1, 'VOUCHER-006');
        $newCount1 = $this->repository->countVoucherLineItemsForCompany($company1);
        self::assertSame(4, $newCount1);
    }

    public function testCountVoucherLineItemsForCompanyWithMultipleInvoices(): void
    {
        $company = $this->createCompany();

        $invoice1 = $this->createInvoice($company);
        $invoice2 = $this->createInvoice($company);

        $creditMemo1 = $this->createCreditMemo($invoice1);
        $creditMemo2 = $this->createCreditMemo($invoice2);

        $this->createCreditMemoLineItem($creditMemo1, 'VOUCHER-101');
        $this->createCreditMemoLineItem($creditMemo1, 'VOUCHER-102');

        $this->createCreditMemoLineItem($creditMemo2, 'VOUCHER-201');
        $this->createCreditMemoLineItem($creditMemo2, 'VOUCHER-202');
        $this->createCreditMemoLineItem($creditMemo2, 'VOUCHER-203');

        $count = $this->repository->countVoucherLineItemsForCompany($company);
        self::assertSame(5, $count);
    }
}
