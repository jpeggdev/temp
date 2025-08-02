<?php

namespace App\QueryBuilder;

use App\DTO\Query\Invoice\DailySalesQueryDTO;
use App\Entity\Invoice;
use DateTimeInterface;
use Doctrine\ORM\QueryBuilder;

readonly class InvoiceQueryBuilder extends AbstractQueryBuilder
{
    protected function createBaseQueryBuilder(): QueryBuilder
    {
        return $this->em->createQueryBuilder()
            ->select('i')
            ->from(Invoice::class, 'i');
    }

    public function createFetchAllByCompanyIdQueryBuilder(
        int $companyId,
        string $sortOrder = 'ASC'
    ): QueryBuilder {
        $queryBuilder = $this->createBaseQueryBuilder();
        $queryBuilder = $this->applyCompanyIdentifierFilter($queryBuilder, $companyId);
        $queryBuilder->orderBy('i.id', $sortOrder);

        return $queryBuilder;
    }

    public function createFetchAllByTradeIdQueryBuilder(
        int $tradeId,
        string $sortOrder = 'ASC'
    ): QueryBuilder {
        $queryBuilder = $this->createBaseQueryBuilder();
        $queryBuilder = $this->applyTradeIdFilter($queryBuilder, $tradeId);
        $queryBuilder->orderBy('i.id', $sortOrder);

        return $queryBuilder;
    }

    public function createFetchAllByCompanyAndTradeIdQueryBuilder(
        int $companyId,
        int $tradeId,
        string $sortOrder = 'ASC'
    ): QueryBuilder {
        $queryBuilder = $this->createBaseQueryBuilder();
        $queryBuilder = $this->applyCompanyIdentifierFilter($queryBuilder, $companyId);
        $queryBuilder = $this->applyTradeIdFilter($queryBuilder, $tradeId);
        $queryBuilder->orderBy('i.id', $sortOrder);

        return $queryBuilder;
    }

    public function createFetchDMERDailySalesDataDTOQueryBuilder(
        DailySalesQueryDTO $dailySalesQueryDTO
    ): QueryBuilder {
        $queryBuilder = $this->createBaseQueryBuilder();

        if ($dailySalesQueryDTO->companyIdentifier) {
            $queryBuilder = $this->applyCompanyIdentifierFilter($queryBuilder, $dailySalesQueryDTO->companyIdentifier);
        }
        if (!empty($dailySalesQueryDTO->startDate)) {
            $queryBuilder = $this->applyInvoicedAtStartDate($queryBuilder, $dailySalesQueryDTO->startDate);
        }
        if (!empty($dailySalesQueryDTO->endDate)) {
            $queryBuilder = $this->applyInvoicedAtEndDate($queryBuilder, $dailySalesQueryDTO->endDate);
        }

        $queryBuilder->orderBy($dailySalesQueryDTO->orderBy, $dailySalesQueryDTO->sortOrder);

        return $queryBuilder;
    }

    private function applyInvoicedAtStartDate(QueryBuilder $queryBuilder, DateTimeInterface $startDate): QueryBuilder
    {
        return $queryBuilder
            ->andWhere('i.invoicedAt >= :startDate')
            ->setParameter('startDate', $startDate);
    }

    private function applyInvoicedAtEndDate(QueryBuilder $queryBuilder, DateTimeInterface $endDate): QueryBuilder
    {
        return $queryBuilder
            ->andWhere('i.invoicedAt < :endDate')
            ->setParameter('endDate', $endDate);
    }

    private function applyCompanyIdentifierFilter(
        QueryBuilder $queryBuilder,
        string $identifier
    ): QueryBuilder {
        return $queryBuilder
            ->innerJoin('i.company', 'co')
            ->andWhere('co.identifier = :identifier')
            ->setParameter('identifier', $identifier);
    }

    private function applyTradeIdFilter(
        QueryBuilder $queryBuilder,
        int $tradeId
    ): QueryBuilder {
        return $queryBuilder
            ->andWhere('i.trade = :tradeId')
            ->setParameter('tradeId', $tradeId);
    }

    private function applyInvoiceYearsFilter(
        QueryBuilder $queryBuilder,
        array $years
    ): QueryBuilder {
        $orX = $queryBuilder->expr()->orX();

        foreach ($years as $year) {
            $yearStartDate = new \DateTime("$year-01-01");
            $yearEndDate = new \DateTime("$year-12-31");
            $yearStartDateParameter = ':' . 'year_' . $year . '_start';
            $yearEndDateParameter = ':' . 'year_' . $year . '_end';

            $orX->add(
                $queryBuilder->expr()->between(
                    'i.invoicedAt',
                    $yearStartDateParameter,
                    $yearEndDateParameter,
                )
            );

            $queryBuilder->setParameter($yearStartDateParameter, $yearStartDate);
            $queryBuilder->setParameter($yearEndDateParameter, $yearEndDate);
        }

        $queryBuilder->andWhere($orX);

        return $queryBuilder;
    }
}
