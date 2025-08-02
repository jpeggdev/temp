<?php

namespace App\Repository;

use App\DTO\Request\Resource\GetResourceSearchResultsQueryDTO;
use App\Entity\Employee;
use App\Entity\ResourceType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ResourceType>
 */
class ResourceTypeRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
    ) {
        parent::__construct($registry, ResourceType::class);
    }

    public function findAllWithResourceCounts(): array
    {
        return $this->createQueryBuilder('rt')
            ->select('rt.id AS id, rt.name AS name, COUNT(r.id) AS resourceCount')
            ->leftJoin('rt.resources', 'r')
            ->groupBy('rt.id')
            ->orderBy('rt.sortOrder', 'ASC')
            ->getQuery()
            ->getArrayResult();
    }

    public function findAllWithFilteredResourceCounts(
        GetResourceSearchResultsQueryDTO $dto,
        ?Employee $employee = null,
    ): array {
        $subDql = <<<DQL
            SELECT COUNT(r2.id)
            FROM App\Entity\Resource r2
            WHERE r2.type = rt
              AND r2.isPublished = true
              AND (
                r2.publishStartDate IS NULL
                OR r2.publishStartDate <= :now
              )
              AND (
                r2.publishEndDate IS NULL
                OR r2.publishEndDate >= :now
              )
        DQL;

        if (!empty($dto->searchTerm)) {
            $subDql .= <<<DQL
                AND TSMATCH(r2.searchVector, WEBCSEARCH_TO_TSQUERY('english', :searchTerm)) = true
            DQL;
        }

        if ($dto->showFavorites && null !== $employee) {
            $subDql .= <<<DQL
                AND r2.id IN (
                  SELECT r3.id
                  FROM App\Entity\ResourceFavorite rf
                  JOIN rf.resource r3
                  WHERE rf.employee = :employeeId
                )
            DQL;
        }

        $qb = $this->getEntityManager()->createQueryBuilder();

        $qb->select([
            'rt.id AS id',
            'rt.name AS name',
            'rt.icon AS icon',
            'rt.primaryIcon AS primaryIcon',
            '('.$subDql.') AS resourceCount',
        ])
            ->from(ResourceType::class, 'rt')
            ->orderBy('rt.name', 'ASC');

        $qb->setParameter('now', new \DateTimeImmutable());

        if (!empty($dto->searchTerm)) {
            $qb->setParameter('searchTerm', $dto->searchTerm);
        }

        if ($dto->showFavorites && null !== $employee) {
            $qb->setParameter('employeeId', $employee->getId());
        }

        return $qb->getQuery()->getArrayResult();
    }
}
