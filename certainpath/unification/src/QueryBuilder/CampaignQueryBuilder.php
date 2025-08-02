<?php

namespace App\QueryBuilder;

use App\Entity\Campaign;
use App\Entity\CampaignStatus;
use Doctrine\ORM\QueryBuilder;

readonly class CampaignQueryBuilder extends AbstractQueryBuilder
{
    protected function createBaseQueryBuilder(): QueryBuilder
    {
        return $this->em->createQueryBuilder()
            ->select('ca')
            ->from(Campaign::class, 'ca')
            ->leftJoin('ca.campaignStatus', 'cs');
    }

    public function createFindByIdQueryBuilder(int $id): QueryBuilder
    {
        $queryBuilder = $this->createBaseQueryBuilder();

        return $this->applyIdFilter($queryBuilder, $id);
    }

    public function createFindByNameQueryBuilder(string $name): QueryBuilder
    {
        $queryBuilder = $this->createBaseQueryBuilder();

        return $this->applyNameFilter($queryBuilder, $name);
    }

    public function createFetchAllByCompanyIdQueryBuilder(
        int $companyId,
        string $sortOrder = 'DESC'
    ): QueryBuilder {
        $queryBuilder = $this->createBaseQueryBuilder();
        $queryBuilder = $this->applyCompanyIdFilter($queryBuilder, $companyId);
        $queryBuilder->orderBy('ca.id', $sortOrder);

        return $queryBuilder;
    }

    public function createFetchAllByCompanyIdentifierQueryBuilder(
        string $companyIdentifier,
        string $sortOrder = 'DESC',
    ): QueryBuilder {
        $queryBuilder = $this->createBaseQueryBuilder();
        $queryBuilder = $this->applyCompanyIdentifierFilter($queryBuilder, $companyIdentifier);
        $queryBuilder->orderBy('ca.id', $sortOrder);

        return $queryBuilder;
    }

    public function createFetchAllByCompanyIdentifierAndStatusIdQueryBuilder(
        string $companyIdentifier,
        ?int $campaignStatusId = null,
        string $sortOrder = 'DESC',
    ): QueryBuilder {
        $queryBuilder = $this->createFetchAllByCompanyIdentifierQueryBuilder($companyIdentifier);

        if ($campaignStatusId) {
            $queryBuilder = $this->applyCampaignStatusFilter($queryBuilder, $campaignStatusId);
        }

        $queryBuilder->orderBy('ca.id', $sortOrder);

        return $queryBuilder;
    }

    public function createFetchAllActiveQueryBuilder($sortOrder = 'DESC'): QueryBuilder
    {
        $queryBuilder = $this->createBaseQueryBuilder();
        $queryBuilder = $this->applyActiveCompanyFilter($queryBuilder);
        $queryBuilder = $this->applyActiveCampaignFilter($queryBuilder);
        $queryBuilder->orderBy('ca.id', $sortOrder);

        return $queryBuilder;
    }

    public function createFetchAllActiveByCompanyIdQueryBuilder(
        int $companyId,
        string $sortOrder = 'DESC'
    ): QueryBuilder {
        $queryBuilder = $this->createBaseQueryBuilder();
        $queryBuilder = $this->applyCompanyIdFilter($queryBuilder, $companyId);
        $queryBuilder = $this->applyActiveCompanyFilter($queryBuilder);
        $queryBuilder = $this->applyActiveCampaignFilter($queryBuilder);
        $queryBuilder->orderBy('ca.id', $sortOrder);

        return $queryBuilder;
    }

    private function applyIdFilter(
        QueryBuilder $queryBuilder,
        int $id
    ): QueryBuilder {
        return $queryBuilder
            ->andWhere('ca.id = :id')
            ->setParameter('id', $id);
    }

    private function applyCompanyIdFilter(
        QueryBuilder $queryBuilder,
        int $companyId
    ): QueryBuilder {
        return $queryBuilder
            ->leftJoin('ca.company', 'co')
            ->andWhere('co.id = :companyId')
            ->setParameter('companyId', $companyId);
    }

    private function applyCompanyIdentifierFilter(
        QueryBuilder $queryBuilder,
        string $identifier
    ): QueryBuilder {
        return $queryBuilder
            ->leftJoin('ca.company', 'co')
            ->andWhere('co.identifier = :identifier')
            ->setParameter('identifier', $identifier);
    }

    private function applyActiveCampaignFilter(QueryBuilder $queryBuilder): QueryBuilder
    {
        $queryBuilder = $this->applyIsActiveFilter($queryBuilder);
        $queryBuilder = $this->applyIsDeletedFilter($queryBuilder);

        if (!$this->aliasExists($queryBuilder, 'cs')) {
            $queryBuilder->innerJoin('ca.campaignStatus', 'cs');
        }

        return $queryBuilder
            ->andWhere('cs.name = :statusActive')
            ->andWhere('ca.deletedAt IS NULL')
            ->setParameter('statusActive', CampaignStatus::STATUS_ACTIVE);
    }

    private function applyActiveCompanyFilter(QueryBuilder $queryBuilder,): QueryBuilder
    {
        if (!$this->aliasExists($queryBuilder, 'co')) {
            $queryBuilder->innerJoin('ca.company', 'co');
        }

        return $queryBuilder
            ->andWhere('co.isActive = :isActive')
            ->andWhere('co.isDeleted = :isDeleted')
            ->andWhere('co.deletedAt IS NULL')
            ->setParameter('isActive', true)
            ->setParameter('isDeleted', false);
    }

    private function applyNameFilter(
        QueryBuilder $queryBuilder,
        string $name
    ): QueryBuilder {
        return $queryBuilder
            ->andWhere('ca.name = :name')
            ->setParameter('name', $name);
    }

    private function applyIsActiveFilter(
        QueryBuilder $queryBuilder,
        bool $isActive = true
    ): QueryBuilder {
        return $queryBuilder
            ->andWhere('ca.isActive = :isActive')
            ->setParameter('isActive', $isActive);
    }

    private function applyIsDeletedFilter(
        QueryBuilder $queryBuilder,
        bool $isDeleted = false
    ): QueryBuilder {
        return $queryBuilder
            ->andWhere('ca.isDeleted = :isDeleted')
            ->setParameter('isDeleted', $isDeleted);
    }

    private function applyCampaignStatusFilter(
        QueryBuilder $queryBuilder,
        int $campaignStatusId
    ): QueryBuilder {
        return $queryBuilder
            ->andWhere('cs.id = :campaignStatusId')
            ->setParameter('campaignStatusId', $campaignStatusId);
    }
}
