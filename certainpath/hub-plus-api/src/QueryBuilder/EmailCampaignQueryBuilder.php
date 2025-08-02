<?php

namespace App\QueryBuilder;

use App\Entity\EmailCampaign;
use App\Module\EmailManagement\Feature\EmailCampaignManagement\DTO\Query\GetEmailCampaignsDTO;
use App\QueryBuilder\Filter\IdFilterTrait;
use Doctrine\ORM\QueryBuilder;

readonly class EmailCampaignQueryBuilder extends AbstractQueryBuilder
{
    use IdFilterTrait;

    public const string ALIAS = 'ec';

    protected function createBaseQueryBuilder(): QueryBuilder
    {
        return $this->em->createQueryBuilder()
            ->select('ec')
            ->from(EmailCampaign::class, 'ec');
    }

    public function createBaseCountQueryBuilder(): QueryBuilder
    {
        return $this->em->createQueryBuilder()
            ->select('COUNT(ec.id)')
            ->from(EmailCampaign::class, 'ec');
    }

    public function createFindOneByIdQueryBuilder(int $id): QueryBuilder
    {
        $queryBuilder = $this->createBaseQueryBuilder();

        return $this->applyIdFilter($queryBuilder, self::ALIAS, $id);
    }

    public function createFindAllByDTOQueryBuilder(GetEmailCampaignsDTO $queryDTO): QueryBuilder
    {
        $queryBuilder = $this->createBaseQueryBuilder();
        $queryBuilder = $this->applyFilters($queryBuilder, $queryDTO);

        $queryBuilder->setMaxResults($queryDTO->pageSize);
        $queryBuilder->setFirstResult(($queryDTO->page - 1) * $queryDTO->pageSize);
        $queryBuilder->orderBy('ec.'.$queryDTO->sortBy, $queryDTO->sortOrder);

        return $queryBuilder;
    }

    public function createGetCountByDTOQueryBuilder(GetEmailCampaignsDTO $queryDto): QueryBuilder
    {
        $queryBuilder = $this->createBaseCountQueryBuilder();

        return $this->applyFilters($queryBuilder, $queryDto);
    }

    private function applyFilters(
        QueryBuilder $queryBuilder,
        GetEmailCampaignsDTO $queryDTO,
    ): QueryBuilder {
        if ($queryDTO->searchTerm) {
            $queryBuilder = $this->applySearchTermFilter($queryBuilder, $queryDTO->searchTerm);
        }

        if ($queryDTO->emailCampaignStatusId) {
            $queryBuilder = $this->applyEmailCampaignStatusIdFilter($queryBuilder, $queryDTO->emailCampaignStatusId);
        }

        return $queryBuilder;
    }

    private function applySearchTermFilter(
        QueryBuilder $queryBuilder,
        string $searchTerm,
    ): QueryBuilder {
        $queryBuilder
            ->andWhere('LOWER(ec.campaignName) LIKE LOWER(:searchTerm)')
            ->setParameter('searchTerm', '%'.strtolower($searchTerm).'%');

        return $queryBuilder;
    }

    private function applyEmailCampaignStatusIdFilter(
        QueryBuilder $queryBuilder,
        int $emailCampaignStatusId,
    ): QueryBuilder {
        if (!$this->aliasExists($queryBuilder, 'ecs')) {
            $queryBuilder->innerJoin('ec.emailCampaignStatus', 'ecs');
        }

        $queryBuilder->andWhere('ecs.id = :emailCampaignStatusId');
        $queryBuilder->setParameter('emailCampaignStatusId', $emailCampaignStatusId);

        return $queryBuilder;
    }
}
