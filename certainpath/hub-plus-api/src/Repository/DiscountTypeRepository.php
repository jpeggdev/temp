<?php

namespace App\Repository;

use App\Entity\DiscountType;
use App\Module\EventRegistration\Feature\Shared\DiscountType\Exception\DiscountTypeNotFoundException;
use App\QueryBuilder\DiscountTypeQueryBuilder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DiscountType>
 */
class DiscountTypeRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly DiscountTypeQueryBuilder $discountTypeQueryBuilder,
    ) {
        parent::__construct($registry, DiscountType::class);
    }

    public function findOneById(int $id): ?DiscountType
    {
        return $this->discountTypeQueryBuilder
            ->createFindOneByIdQueryBuilder($id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findOneByIdOrFail(int $id): DiscountType
    {
        $result = $this->findOneById($id);

        if (!$result) {
            throw new DiscountTypeNotFoundException();
        }

        return $result;
    }

    public function findOneByNameOrFail(string $name): DiscountType
    {
        $result = $this->discountTypeQueryBuilder
            ->createFindOneByNameQueryBuilder($name)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$result) {
            throw new DiscountTypeNotFoundException();
        }

        return $result;
    }

    public function findOneByName(string $name): ?DiscountType
    {
        return $this->createQueryBuilder('dt')
            ->where('dt.name = :name')
            ->setParameter('name', $name)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
