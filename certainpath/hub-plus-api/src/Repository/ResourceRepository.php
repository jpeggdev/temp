<?php

declare(strict_types=1);

namespace App\Repository;

use App\DTO\Request\Resource\GetResourceSearchResultsQueryDTO;
use App\DTO\Request\Resource\GetResourcesRequestDTO;
use App\Entity\Employee;
use App\Entity\Resource;
use App\QueryBuilder\ResourceQueryBuilder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Resource>
 */
class ResourceRepository extends ServiceEntityRepository
{
    public const string ALIAS = 'r';

    public function __construct(
        ManagerRegistry $registry,
        private readonly ResourceQueryBuilder $resourceQueryBuilder,
    ) {
        parent::__construct($registry, Resource::class);
    }

    public function remove(Resource $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function save(Resource $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    protected function createBaseCountQueryBuilder(): QueryBuilder
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->select('COUNT('.self::ALIAS.'.id)')
            ->from(Resource::class, self::ALIAS);
    }

    public function findBySlug(string $slug): ?Resource
    {
        return $this->findOneBy(['slug' => $slug]);
    }

    public function findResourcesByQuery(GetResourcesRequestDTO $queryDto): array
    {
        $qb = $this->resourceQueryBuilder->createFindResourcesByQueryBuilder($queryDto);

        return $qb->getQuery()->getResult();
    }

    public function getTotalCount(GetResourcesRequestDTO $queryDto): int
    {
        $qb = $this->resourceQueryBuilder->createCountResourcesByQueryBuilder($queryDto);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    public function findPublishedResourcesByQuery(
        GetResourceSearchResultsQueryDTO $queryDto,
        ?Employee $employee = null,
    ): array {
        $qb = $this->resourceQueryBuilder->createFindPublishedResourcesByQueryBuilder($queryDto, $employee);

        return $qb->getQuery()->getResult();
    }

    public function getPublishedTotalCount(
        GetResourceSearchResultsQueryDTO $queryDto,
        ?Employee $employee = null,
    ): int {
        $qb = $this->resourceQueryBuilder->createCountPublishedResourcesByQueryBuilder($queryDto, $employee);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    public function getCountByResourceTypeName(string $resourceTypeName): int
    {
        $queryBuilder = $this->createBaseCountQueryBuilder();
        $queryBuilder = $this->applyResourceTypeNameFilter($queryBuilder, $resourceTypeName);

        return $queryBuilder
            ->getQuery()
            ->getSingleScalarResult();
    }

    private function applyResourceTypeNameFilter(
        QueryBuilder $queryBuilder,
        string $resourceTypeName,
    ): QueryBuilder {
        if (!in_array('rt', $queryBuilder->getAllAliases(), true)) {
            $queryBuilder->innerJoin(self::ALIAS.'.resourceType', 'rt');
        }

        return $queryBuilder
            ->andWhere('rt.name = :resourceTypeName')
            ->setParameter('resourceTypeName', $resourceTypeName);
    }
}
