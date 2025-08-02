<?php

declare(strict_types=1);

namespace App\Repository\EventSession;

use Doctrine\ORM\QueryBuilder;

trait EventSessionQueryBuilderTrait
{
    private function applyIdFilter(QueryBuilder $qb, int $id): static
    {
        $qb->andWhere('es.id = :id')
            ->setParameter('id', $id);

        return $this;
    }

    private function applyUuidFilter(QueryBuilder $qb, string $uuid): static
    {
        $qb->andWhere('es.uuid = :uuid')
            ->setParameter('uuid', $uuid);

        return $this;
    }

    private function applyPaginationAndSorting(
        QueryBuilder $qb,
        int $page,
        ?int $pageSize,
        ?string $sortBy,
        ?string $sortOrder,
        string $defaultSortBy = 'startDate',
        string $defaultSortOrder = 'asc',
    ): static {
        $size = $pageSize ?? 10;
        $qb->setMaxResults($size)
            ->setFirstResult(($page - 1) * $size);

        if ($sortBy) {
            $qb->orderBy('es.'.$sortBy, $sortOrder ?? 'asc');
        } else {
            $qb->orderBy('es.'.$defaultSortBy, $defaultSortOrder);
        }

        return $this;
    }

    private function applyEventIdFilter(QueryBuilder $qb, ?int $eventId): static
    {
        if (null !== $eventId) {
            $qb->andWhere('es.event = :eventId')
                ->setParameter('eventId', $eventId);
        }

        return $this;
    }

    private function applyEventUuidFilter(QueryBuilder $qb, ?string $eventUuid): static
    {
        if (null !== $eventUuid) {
            $qb->innerJoin('es.event', 'e')
                ->andWhere('e.uuid = :uuid')
                ->setParameter('uuid', $eventUuid);
        }

        return $this;
    }

    private function applyIsPublishedFilter(QueryBuilder $qb, ?bool $isPublished): static
    {
        if (null !== $isPublished) {
            $qb->andWhere('es.isPublished = :pub')
                ->setParameter('pub', $isPublished);
        }

        return $this;
    }

    private function applySearchTermFilter(QueryBuilder $qb, ?string $searchTerm): static
    {
        if (!empty($searchTerm)) {
            // Example approach: cast the date to string and check if it contains the searchTerm
            // for partial matching.  Adjust for your environment:
            $qb->andWhere('CAST(es.startDate as string) LIKE :searchTerm')
                ->setParameter('searchTerm', '%'.$searchTerm.'%');
        }

        return $this;
    }
}
