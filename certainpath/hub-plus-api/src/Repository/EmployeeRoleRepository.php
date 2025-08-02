<?php

namespace App\Repository;

use App\DTO\Request\EmployeeRole\GetEmployeeRolesRequestDTO;
use App\DTO\Request\Event\GetEventSearchResultsQueryDTO;
use App\Entity\Employee;
use App\Entity\EmployeeRole;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EmployeeRole>
 */
class EmployeeRoleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EmployeeRole::class);
    }

    public function save(EmployeeRole $employeeRole, bool $flush = false): void
    {
        $this->getEntityManager()->persist($employeeRole);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findOneByName(string $name): ?EmployeeRole
    {
        return $this->createQueryBuilder('er')
            ->andWhere('er.name = :name')
            ->setParameter('name', $name)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return EmployeeRole[]
     */
    public function findRolesByQuery(GetEmployeeRolesRequestDTO $dto): array
    {
        $qb = $this->createQueryBuilder('er');
        $this->applyFilters($qb, $dto);
        $qb->orderBy('er.'.$dto->sortBy, $dto->sortOrder)
            ->setFirstResult(($dto->page - 1) * $dto->pageSize)
            ->setMaxResults($dto->pageSize);

        return $qb->getQuery()->getResult();
    }

    public function countRolesByQuery(GetEmployeeRolesRequestDTO $dto): int
    {
        $qb = $this->createQueryBuilder('er')
            ->select('COUNT(er.id)');
        $this->applyFilters($qb, $dto);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Return each EmployeeRole + eventCount using a sub-DQL approach.
     */
    public function findAllWithFilteredEventCounts(
        GetEventSearchResultsQueryDTO $dto,
        ?Employee $employee = null,
    ): array {
        $subDql = <<<DQL
    SELECT COUNT(e2.id)
    FROM App\Entity\Event e2
    JOIN e2.eventEmployeeRoleMappings erm2
    WHERE e2.isPublished = true
      AND erm2.employeeRole = er
DQL;

        // if there's a searchTerm
        if (!empty($dto->searchTerm)) {
            $subDql .= <<<DQL
        AND TSMATCH(e2.searchVector, WEBCSEARCH_TO_TSQUERY('english', :searchTerm)) = true
    DQL;
        }

        // if eventTypeIds
        if (!empty($dto->eventTypeIds)) {
            $subDql .= <<<DQL
        AND e2.eventType IN (:eventTypeIds)
    DQL;
        }

        // if categoryIds
        if (!empty($dto->categoryIds)) {
            $subDql .= <<<DQL
        AND e2.eventCategory IN (:categoryIds)
    DQL;
        }

        // if tradeIds
        if (!empty($dto->tradeIds)) {
            $subDql .= <<<DQL
        AND e2.id IN (
          SELECT e3.id
          FROM App\Entity\EventTradeMapping etm
          JOIN etm.event e3
          WHERE etm.trade IN (:tradeIds)
        )
    DQL;
        }

        // if favorites
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

        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select([
            'er.id AS id',
            'er.name AS name',
            '('.$subDql.') AS eventCount',
        ])
            ->from(EmployeeRole::class, 'er')
            ->orderBy('er.name', 'ASC');

        // Bind parameters as needed
        if (!empty($dto->searchTerm)) {
            $qb->setParameter('searchTerm', $dto->searchTerm);
        }
        if (!empty($dto->eventTypeIds)) {
            $qb->setParameter('eventTypeIds', $dto->eventTypeIds);
        }
        if (!empty($dto->categoryIds)) {
            $qb->setParameter('categoryIds', $dto->categoryIds);
        }
        if (!empty($dto->tradeIds)) {
            $qb->setParameter('tradeIds', $dto->tradeIds);
        }
        if ($dto->showFavorites && null !== $employee) {
            $qb->setParameter('employeeId', $employee->getId());
        }

        return $qb->getQuery()->getArrayResult();
    }

    private function applyFilters(QueryBuilder $qb, GetEmployeeRolesRequestDTO $dto): void
    {
        if ($dto->name) {
            $qb->andWhere('LOWER(er.name) LIKE LOWER(:searchName)')
                ->setParameter('searchName', '%'.strtolower($dto->name).'%');
        }
    }
}
