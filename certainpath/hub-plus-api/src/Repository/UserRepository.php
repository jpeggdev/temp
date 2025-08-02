<?php

declare(strict_types=1);

namespace App\Repository;

use App\DTO\Request\UserQueryDTO;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function save(User $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findUsersByQuery(UserInterface $user, UserQueryDTO $queryDto): array
    {
        $qb = $this->createQueryBuilder('u')
            ->setMaxResults($queryDto->page * 10)
            ->setFirstResult(($queryDto->page - 1) * 10)
            ->orderBy('u.'.$queryDto->sortBy, $queryDto->sortOrder);

        $this->applyFilters($qb, $queryDto);

        return $qb->getQuery()->getResult();
    }

    public function getTotalCount(UserInterface $user, UserQueryDTO $queryDto): int
    {
        $qb = $this->createQueryBuilder('u')
            ->select('COUNT(u.id)');

        $this->applyFilters($qb, $queryDto);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    private function applyFilters(QueryBuilder $qb, UserQueryDTO $queryDto): void
    {
        if ($queryDto->firstName) {
            $qb->andWhere('LOWER(u.firstName) LIKE LOWER(:firstName)')
                ->setParameter('firstName', '%'.strtolower($queryDto->firstName).'%');
        }

        if ($queryDto->lastName) {
            $qb->andWhere('LOWER(u.lastName) LIKE LOWER(:lastName)')
                ->setParameter('lastName', '%'.strtolower($queryDto->lastName).'%');
        }

        if ($queryDto->email) {
            $qb->andWhere('LOWER(u.email) LIKE LOWER(:email)')
                ->setParameter('email', '%'.strtolower($queryDto->email).'%');
        }

        if ($queryDto->salesforceId) {
            $qb->andWhere('u.salesforceId = :salesforceId')
                ->setParameter('salesforceId', $queryDto->salesforceId);
        }
    }

    public function findOneByEmail(string $email): ?User
    {
        return $this->findOneBy(['email' => $email]);
    }

    public function findOneBySsoId(string $id): ?User
    {
        return $this->findOneBy(['ssoId' => $id]);
    }
}
