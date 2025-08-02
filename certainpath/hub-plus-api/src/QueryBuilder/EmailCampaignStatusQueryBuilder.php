<?php

namespace App\QueryBuilder;

use App\DTO\Query\EmailCampaignStatuses\GetEmailCampaignStatusesDTO;
use App\Entity\EmailCampaignStatus;
use Doctrine\ORM\QueryBuilder;

readonly class EmailCampaignStatusQueryBuilder extends AbstractQueryBuilder
{
    protected function createBaseQueryBuilder(): QueryBuilder
    {
        return $this->em->createQueryBuilder()
            ->select('ecs')
            ->from(EmailCampaignStatus::class, 'ecs');
    }

    public function createBaseCountQueryBuilder(): QueryBuilder
    {
        return $this->em->createQueryBuilder()
            ->select('COUNT(ecs.id)')
            ->from(EmailCampaignStatus::class, 'ecs');
    }

    public function createFindOneByNameQueryBuilder(string $name): QueryBuilder
    {
        $queryBuilder = $this->createBaseQueryBuilder();

        return $this->applyNameFilter($queryBuilder, $name);
    }

    public function createFindAllByDTOQueryBuilder(GetEmailCampaignStatusesDTO $queryDTO): QueryBuilder
    {
        $queryBuilder = $this->createBaseQueryBuilder();
        $queryBuilder = $this->applyFilters($queryBuilder, $queryDTO);

        $queryBuilder->setMaxResults($queryDTO->pageSize);
        $queryBuilder->setFirstResult(($queryDTO->page - 1) * $queryDTO->pageSize);
        $queryBuilder->orderBy('ecs.'.$queryDTO->sortBy, $queryDTO->sortOrder);

        return $queryBuilder;
    }

    private function applyNameFilter(
        QueryBuilder $queryBuilder,
        string $name,
    ): QueryBuilder {
        $queryBuilder->andWhere('ecs.name = :name');
        $queryBuilder->setParameter('name', $name);

        return $queryBuilder;
    }

    private function applyFilters(
        QueryBuilder $queryBuilder,
        GetEmailCampaignStatusesDTO $queryDTO,
    ): QueryBuilder {
        if ($queryDTO->searchTerm) {
            $queryBuilder = $this->applySearchTermFilter($queryBuilder, $queryDTO->searchTerm);
        }

        return $queryBuilder;
    }

    private function applySearchTermFilter(
        QueryBuilder $queryBuilder,
        string $searchTerm,
    ): QueryBuilder {
        $queryBuilder
            ->andWhere('LOWER(ecs.name) LIKE LOWER(:searchTerm)')
            ->setParameter('searchTerm', '%'.strtolower($searchTerm).'%');

        return $queryBuilder;
    }

    public function createGetCountByDTOQueryBuilder(GetEmailCampaignStatusesDTO $queryDto): QueryBuilder
    {
        $queryBuilder = $this->createBaseCountQueryBuilder();

        return $this->applyFilters($queryBuilder, $queryDto);
    }
}
