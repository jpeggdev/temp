<?php

declare(strict_types=1);

namespace App\Repository\EventRepository;

use App\Entity\Employee;
use Doctrine\ORM\QueryBuilder;

trait EventQueryBuilderTrait
{
    private function applyIdFilter(QueryBuilder $qb, int $id): static
    {
        $qb->andWhere('e.id = :id')
            ->setParameter('id', $id);

        return $this;
    }

    private function applyUuidFilter(QueryBuilder $qb, string $uuid): static
    {
        $qb->andWhere('e.uuid = :uuid')
            ->setParameter('uuid', $uuid);

        return $this;
    }

    private function applySearchTermFilter(QueryBuilder $qb, ?string $searchTerm): static
    {
        if ($searchTerm) {
            $qb->andWhere('TSMATCH(e.searchVector, WEBCSEARCH_TO_TSQUERY(\'english\', :searchTerm)) = true')
                ->setParameter('searchTerm', $searchTerm);
        }

        return $this;
    }

    private function applyTradeIdsFilter(QueryBuilder $qb, ?array $tradeIds): static
    {
        if (!empty($tradeIds)) {
            $qb->innerJoin('e.eventTradeMappings', 'etm')
                ->andWhere('etm.trade IN (:tradeIds)')
                ->setParameter('tradeIds', $tradeIds);
        }

        return $this;
    }

    private function applyCategoryIdsFilter(QueryBuilder $qb, ?array $categoryIds): static
    {
        if (!empty($categoryIds)) {
            $qb->andWhere('e.eventCategory IN (:catIds)')
                ->setParameter('catIds', $categoryIds);
        }

        return $this;
    }

    private function applyEmployeeRoleIdsFilter(QueryBuilder $qb, ?array $employeeRoleIds): static
    {
        if (!empty($employeeRoleIds)) {
            $qb->innerJoin('e.eventEmployeeRoleMappings', 'erm')
                ->andWhere('erm.employeeRole IN (:roleIds)')
                ->setParameter('roleIds', $employeeRoleIds);
        }

        return $this;
    }

    private function applyTagIdsFilter(QueryBuilder $qb, ?array $tagIds): static
    {
        if (!empty($tagIds)) {
            $qb->innerJoin('e.eventTagMappings', 'etag')
                ->andWhere('etag.eventTag IN (:tagIds)')
                ->setParameter('tagIds', $tagIds);
        }

        return $this;
    }

    private function applyEventTypeIdsFilter(QueryBuilder $qb, ?array $eventTypeIds): static
    {
        if (!empty($eventTypeIds)) {
            $qb->andWhere('e.eventType IN (:typeIds)')
                ->setParameter('typeIds', $eventTypeIds);
        }

        return $this;
    }

    private function applyShowFavoritesFilter(QueryBuilder $qb, Employee $employee): static
    {
        $qb->innerJoin('e.eventFavorites', 'fav')
            ->andWhere('fav.employee = :employeeId')
            ->setParameter('employeeId', $employee->getId());

        return $this;
    }

    private function applyPaginationAndSorting(
        QueryBuilder $qb,
        int $page,
        ?int $pageSize,
        ?string $sortBy,
        ?string $sortOrder,
        string $defaultSortBy = 'createdAt',
        string $defaultSortOrder = 'DESC',
    ): static {
        $size = $pageSize ?? 10;
        $qb->setMaxResults($size)
            ->setFirstResult(($page - 1) * $size);

        if ($sortBy) {
            $qb->orderBy('e.'.$sortBy, $sortOrder ?? 'asc');
        } else {
            $qb->orderBy('e.'.$defaultSortBy, $defaultSortOrder);
        }

        return $this;
    }
}
