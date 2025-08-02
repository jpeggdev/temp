<?php

namespace App\Repository;

use App\DTO\Request\Event\GetEventSearchResultsQueryDTO;
use App\Entity\Employee;
use App\Entity\Trade;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Trade>
 */
class TradeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Trade::class);
    }

    public function saveTrade(Trade $trade): void
    {
        $this->getEntityManager()->persist($trade);
        $this->getEntityManager()->flush();
    }

    public function getTrade(Trade $trade): ?Trade
    {
        return $this->findOneBy(
            [
                'name' => $trade->getName(),
            ]
        );
    }

    public function initializeTrades(): void
    {
        $this->verifyTrade(Trade::electrical());
        $this->verifyTrade(Trade::hvac());
        $this->verifyTrade(Trade::plumbing());
        $this->verifyTrade(Trade::roofing());
    }

    /**
     * @return Trade[]
     */
    public function getAllTrades(): array
    {
        return $this->findAll();
    }

    /**
     * Return an array of trades + eventCount that match the user’s filters.
     */
    public function findAllWithFilteredEventCounts(
        GetEventSearchResultsQueryDTO $dto,
        ?Employee $employee = null,
    ): array {
        // Instead of "WHERE e2.id IN (SELECT etm2.event FROM...)", we do a join:
        // "JOIN e2.eventTradeMappings etm2 WHERE etm2.trade = t" to fix the correlation error.
        $subDql = <<<DQL
            SELECT COUNT(e2.id)
            FROM App\Entity\Event e2
            JOIN e2.eventTradeMappings etm2
            WHERE e2.isPublished = true
              AND etm2.trade = t
        DQL;

        // If there's a search term (PostgreSQL example)
        if (!empty($dto->searchTerm)) {
            $subDql .= <<<DQL
                AND TSMATCH(e2.searchVector, WEBCSEARCH_TO_TSQUERY('english', :searchTerm)) = true
            DQL;
        }

        // Filter by event types
        if (!empty($dto->eventTypeIds)) {
            $subDql .= <<<DQL
                AND e2.eventType IN (:eventTypeIds)
            DQL;
        }

        // Filter by categories
        if (!empty($dto->categoryIds)) {
            $subDql .= <<<DQL
                AND e2.eventCategory IN (:categoryIds)
            DQL;
        }

        // Filter by employee roles
        if (!empty($dto->employeeRoleIds)) {
            $subDql .= <<<DQL
                AND e2.id IN (
                  SELECT e3.id
                  FROM App\Entity\EventEmployeeRoleMapping erm
                  JOIN erm.event e3
                  WHERE erm.employeeRole IN (:employeeRoleIds)
                )
            DQL;
        }

        // If favorites
        if ($dto->showFavorites && null !== $employee) {
            $subDql .= <<<DQL
                AND e2.id IN (
                  SELECT e4.id
                  FROM App\Entity\EventFavorite ef
                  JOIN ef.event e4
                  WHERE ef.employee = :employeeId
                )
            DQL;
        }

        // Notice we do NOT filter by tradeIds here, so we show facet rows for all trades.
        // If you want to only show trades the user has specifically chosen, you'd add it.

        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select([
            't.id AS id',
            't.name AS name',
            '('.$subDql.') AS eventCount',
        ])
            ->from(Trade::class, 't')
            ->orderBy('t.name', 'ASC');

        // Bind parameters
        if (!empty($dto->searchTerm)) {
            $qb->setParameter('searchTerm', $dto->searchTerm);
        }
        if (!empty($dto->eventTypeIds)) {
            $qb->setParameter('eventTypeIds', $dto->eventTypeIds);
        }
        if (!empty($dto->categoryIds)) {
            $qb->setParameter('categoryIds', $dto->categoryIds);
        }
        if (!empty($dto->employeeRoleIds)) {
            $qb->setParameter('employeeRoleIds', $dto->employeeRoleIds);
        }
        if ($dto->showFavorites && null !== $employee) {
            $qb->setParameter('employeeId', $employee->getId());
        }

        return $qb->getQuery()->getArrayResult();
    }

    private function verifyTrade(Trade $trade): void
    {
        if ($existing = $this->getTrade($trade)) {
            $existing->updateFromReference($trade);
            $this->saveTrade($existing);

            return;
        }
        $this->saveTrade(
            $trade
        );
    }
}
