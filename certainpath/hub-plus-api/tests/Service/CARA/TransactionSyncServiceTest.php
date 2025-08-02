<?php

namespace App\Tests\Service\CARA;

use App\Entity\Company;
use App\Entity\CreditMemo;
use App\Entity\CreditMemoLineItem;
use App\Entity\Employee;
use App\Entity\Invoice;
use App\Entity\InvoiceLineItem;
use App\Entity\Payment;
use App\Entity\PaymentInvoice;
use App\Entity\User;
use App\Enum\CreditMemoStatusType;
use App\Enum\CreditMemoType;
use App\Enum\InvoiceStatusType;
use App\Exception\CARA\CaraAPIException;
use App\Repository\CompanyRepository;
use App\Repository\CreditMemoLineItemRepository;
use App\Repository\CreditMemoRepository;
use App\Repository\EmployeeRepository;
use App\Repository\InvoiceLineItemRepository;
use App\Repository\InvoiceRepository;
use App\Repository\PaymentInvoiceRepository;
use App\Repository\PaymentRepository;
use App\Repository\UserRepository;
use App\Service\CARA\TransactionSyncService;
use App\Tests\AbstractKernelTestCase;

class TransactionSyncServiceTest extends AbstractKernelTestCase
{
    protected CompanyRepository $companyRepository;
    protected InvoiceRepository $invoiceRepository;
    protected InvoiceLineItemRepository $invoiceLineItemRepository;
    protected CreditMemoRepository $creditMemoRepository;
    protected CreditMemoLineItemRepository $creditMemoLineItemRepository;
    protected UserRepository $userRepository;
    protected EmployeeRepository $employeeRepository;
    protected PaymentRepository $paymentRepository;
    protected PaymentInvoiceRepository $paymentInvoiceRepository;
    protected TransactionSyncService $transactionSyncService;

    public function setUp(): void
    {
        parent::setUp();
        $this->companyRepository = $this->getService(CompanyRepository::class);
        $this->invoiceRepository = $this->getService(InvoiceRepository::class);
        $this->invoiceLineItemRepository = $this->getService(InvoiceLineItemRepository::class);
        $this->creditMemoRepository = $this->getService(CreditMemoRepository::class);
        $this->creditMemoLineItemRepository = $this->getService(CreditMemoLineItemRepository::class);
        $this->userRepository = $this->getService(UserRepository::class);
        $this->employeeRepository = $this->getService(EmployeeRepository::class);
        $this->paymentRepository = $this->getService(PaymentRepository::class);
        $this->paymentInvoiceRepository = $this->getService(PaymentInvoiceRepository::class);
        $this->transactionSyncService = $this->getService(TransactionSyncService::class);
    }

    public function testPayloadStructure(): void
    {
        $invoice = $this->createAndSaveInvoiceFromTestData();
        $payload['hubEventTransactions'] = $this->transactionSyncService->mapInvoicesToTransactions([$invoice]);

        $this->assertArrayHasKey('hubEventTransactions', $payload);
        $this->assertIsArray($payload['hubEventTransactions']);
        $this->assertCount(1, $payload['hubEventTransactions']);

        $transaction = $payload['hubEventTransactions'][0];
        $this->assertArrayHasKey('accountingId', $transaction);
        $this->assertArrayHasKey('invoice', $transaction);
        $this->assertArrayHasKey('lines', $transaction['invoice']);
        $this->assertArrayHasKey('creditMemo', $transaction['invoice']);
        $this->assertArrayHasKey('payment', $transaction['invoice']);
    }

    public function testSyncNoTransactions(): void
    {
        $this->expectException(CaraAPIException::class);
        $this->expectExceptionMessage('No transactions to sync.');

        $testData = [];
        $this->transactionSyncService->syncTransactions($testData);
    }

    public function testSyncTransactionsWithNoAccountingId(): void
    {
        $invoice = $this->createAndSaveInvoiceFromTestData();
        $company = $invoice->getCompany();
        $company->setIntacctId(null);
        $company->setSalesforceId(null);
        $this->companyRepository->save($company, true);
        $this->expectException(CaraAPIException::class);
        $this->expectExceptionMessage('Invoice '.$invoice->getUuid().' has no accounting ID.');

        // Test the sync method
        $testData = [$invoice];
        $this->transactionSyncService->syncTransactions($testData);
    }

    private function createAndSaveInvoiceFromTestData(): Invoice
    {
        // Create company
        $company = new Company();
        $company->setCompanyName('Test Company');
        $company->setIntacctId('PS12641');
        $company->setSalesforceId('PS12641');

        $this->companyRepository->save($company);

        // Create invoice
        $invoice = new Invoice();
        $invoice->setInvoiceNumber('INV-1001');
        $invoice->setCompany($company);
        $invoice->setStatus(InvoiceStatusType::PAID);
        $invoice->setTotalAmount('150.00');
        $invoice->setNotes('Thank you for your purchase.');
        $invoice->setInvoiceDate(new \DateTimeImmutable('2024-06-01T12:00:00+00:00'));
        $invoice->setCanBeSynced(true);
        $invoice->setSyncedAt(new \DateTimeImmutable('2024-06-01T12:00:00+00:00'));
        $invoice->setSyncAttempts(0);

        $this->invoiceRepository->save($invoice);

        // Create invoice line items
        $lineItem1 = new InvoiceLineItem();
        $lineItem1->setDescription('Product A');
        $lineItem1->setQuantity(2);
        $lineItem1->setUnitPrice('50.00');
        $lineItem1->setLineTotal('100.00');
        $lineItem1->setDiscountCode('DISC10');

        $this->invoiceLineItemRepository->save($lineItem1);
        $invoice->addInvoiceLineItem($lineItem1);

        $lineItem2 = new InvoiceLineItem();
        $lineItem2->setDescription('Product B');
        $lineItem2->setQuantity(1);
        $lineItem2->setUnitPrice('50.00');
        $lineItem2->setLineTotal('50.00');

        $this->invoiceLineItemRepository->save($lineItem2);
        $invoice->addInvoiceLineItem($lineItem2);

        // Create credit memo
        $creditMemo = new CreditMemo();
        $creditMemo->setCmDate(new \DateTimeImmutable('2024-06-01T12:05:00+00:00'));
        $creditMemo->setStatus(CreditMemoStatusType::POSTED);
        $creditMemo->setTotalAmount('50.00');
        $creditMemo->setReason('Product return');
        $creditMemo->setType(CreditMemoType::VOUCHER);

        $this->creditMemoRepository->save($creditMemo);

        // Create credit memo line item
        $creditMemoLineItem = new CreditMemoLineItem();
        $creditMemoLineItem->setDescription('Refund for item');
        $creditMemoLineItem->setAmount('50.00');
        $creditMemoLineItem->setVoucherCode('SKU-REFUND');

        $creditMemo->addCreditMemoLineItem($creditMemoLineItem);
        $this->creditMemoLineItemRepository->save($creditMemoLineItem);

        $invoice->addCreditMemo($creditMemo);

        // Create User and Employee for Payment
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setFirstName('Test');
        $user->setLastName('User');
        $user->setSsoId('test-sso-id');

        $this->userRepository->save($user);

        $employee = new Employee();
        $employee->setUser($user);
        $employee->setCompany($company);
        $employee->setWorkEmail('test@example.com');
        $employee->setFirstName('Test');
        $employee->setLastName('Employee');

        $this->employeeRepository->save($employee);

        // Create Payment
        $payment = new Payment();
        $payment->setTransactionId('TXN-98765');
        $payment->setAmount('150.00');
        $payment->setCreatedBy($employee);
        $payment->setCustomerProfileId('CUST-001');
        $payment->setPaymentProfileId('PAY-001');
        $payment->setResponseData(['authCode' => 'AUTH123']);
        $payment->setCardType('Visa');
        $payment->setCardLast4('4242');

        $this->paymentRepository->save($payment);

        // Create PaymentInvoice relationship
        $paymentInvoice = new PaymentInvoice();
        $paymentInvoice->setPayment($payment);
        $paymentInvoice->setInvoice($invoice);
        $paymentInvoice->setAppliedAmount('150.00');

        $this->paymentInvoiceRepository->save($paymentInvoice);
        $invoice->addPaymentInvoice($paymentInvoice);

        $this->invoiceRepository->save($invoice, true);

        return $invoice;
    }
}
