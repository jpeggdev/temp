<?php

namespace App\Repository;

use App\Collections\Reporting\DMERSalesCollection;
use App\DTO\Query\Invoice\DailySalesQueryDTO;
use App\DTO\Reports\DMERDailySalesDTO;
use App\Entity\Company;
use App\Entity\Customer;
use App\Entity\Invoice;
use App\Entity\Prospect;
use App\Exceptions\EntityValidationException;
use App\StatementBuilder\InvoicedAtYearsStatementBuilder;
use App\ValueObjects\InvoiceObject;
use App\QueryBuilder\InvoiceQueryBuilder;
use DateInterval;
use DatePeriod;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Validator\Validator\ValidatorInterface;

use function App\Functions\app_getDecimal;

class InvoiceRepository extends AbstractRepository
{

    public function __construct(
        ManagerRegistry $registry,
        private readonly ValidatorInterface $validator,
        private readonly InvoiceQueryBuilder $invoiceQueryBuilder,
        private readonly InvoicedAtYearsStatementBuilder $invoicedAtYearsStatementBuilder,
    ) {
        parent::__construct($registry, Invoice::class);
    }

    public function resolveInvoice(
        Company $company,
        Prospect $prospect,
        InvoiceObject $invoiceObject,
    ): Invoice {
        $invoiceByInvoiceNumber = null;
        if (
            !empty($invoiceObject->invoiceNumber) &&
            $prospect->getCustomer() instanceof Customer
        ) {
            $invoiceByInvoiceNumber = $this->findOneBy([
                'company' => $company,
                'customer' => $prospect->getCustomer(),
                'invoiceNumber' => $invoiceObject->invoiceNumber,
            ]);
        }

        if (
            $invoiceByInvoiceNumber instanceof Invoice
        ) {
            return $invoiceByInvoiceNumber;
        }

        $invoiceByExternalId = $this->findOneBy([
            'company' => $company,
            'externalId' => $invoiceObject->getKey(),
        ]);

        if (
            $invoiceByExternalId instanceof Invoice
        ) {
            return $invoiceByExternalId;
        }

        return (new Invoice())->fromValueObject($invoiceObject);
    }

    /**
     * @throws EntityValidationException
     */
    public function validate(Invoice $invoice): void
    {
        $errors = $this->validator->validate($invoice);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }

            throw new EntityValidationException(implode(' ', $errorMessages));
        }
    }

    public function saveInvoice(Invoice $invoice): Invoice
    {
        /** @var Invoice $saved */
        $saved = $this->save($invoice);
        return $saved;
    }

    public function fetchAllByCompanyId(
        int $companyId,
        string $sortOrder = 'ASC'
    ): ArrayCollection {
        $result = $this->invoiceQueryBuilder
            ->createFetchAllByCompanyIdQueryBuilder($companyId, $sortOrder)
            ->getQuery()
            ->getResult();

        return new ArrayCollection($result);
    }

    /**
     * @return ArrayCollection<int, Invoice>
     */
    public function fetchAllByTradeId(int $tradeId): ArrayCollection
    {
        $result = $this->invoiceQueryBuilder
            ->createFetchAllByTradeIdQueryBuilder($tradeId)
            ->getQuery()
            ->getResult();

        return new ArrayCollection($result);
    }

    /**
     * @return ArrayCollection<int, Invoice>
     */
    public function fetchAllByCompanyAndTradeId(
        int $companyId,
        int $tradeId
    ): ArrayCollection {
        $result = $this->invoiceQueryBuilder
            ->createFetchAllByCompanyAndTradeIdQueryBuilder($companyId, $tradeId)
            ->getQuery()
            ->getResult();

        return new ArrayCollection($result);
    }

    /**
     * @throws DBALException
     */
    public function fetchInvoicedAtYears(
        string $intacctId,
        ?int $page = null,
        ?int $pageSize = null,
        string $sortBy = 'year',
        string $sortOrder = 'DESC',
        ?string $searchTerm = null
    ): ArrayCollection {
        $firstResult = ($page - 1) * $pageSize;

        $result = $this->invoicedAtYearsStatementBuilder
            ->createStatement($intacctId, $pageSize, $firstResult, $sortBy, $sortOrder, $searchTerm)
            ->executeQuery()
            ->fetchAllAssociative();

        $years = array_column($result, 'year');

        return (new ArrayCollection($years));
    }

    /**
     * @throws \DateMalformedPeriodStringException
     */
    public function getDMRDailySalesData(DailySalesQueryDTO $dailySalesQueryDTO): DMERSalesCollection
    {
        $results = $this->invoiceQueryBuilder
            ->createFetchDMERDailySalesDataDTOQueryBuilder($dailySalesQueryDTO)
            ->getQuery()
            ->getResult();

        $start = $dailySalesQueryDTO->startDate;
        $end = $dailySalesQueryDTO->endDate;

        $period = new DatePeriod($start, new DateInterval('P1D'), $end);
        $dates = [ ];
        foreach ($period as $date) {
            $dates[$date->format('Y-m-d')] = DMERDailySalesDTO::createEmptyInstance(
                $date
            );
        }

        foreach ($results as $invoice) {
            $dateKey = $invoice->getInvoicedAt()->format('Y-m-d');
            $dates[$dateKey]->totalSales = (int) $dates[$dateKey]->totalSales + 1;
            $dates[$dateKey]->totalSalesAmount = (float) $dates[$dateKey]->totalSalesAmount +
                (float) $invoice->getTotal();
        }

        $DMERDailySalesDataCollection = new DMERSalesCollection();
        foreach ($dates as $date) {
            $DMERDailySalesDataCollection->add(new DMERDailySalesDTO(
                $date->date,
                $date->totalCalls,
                $date->totalSales,
                app_getDecimal($date->totalSalesAmount)
            ));
        }

        return $DMERDailySalesDataCollection;
    }
}
