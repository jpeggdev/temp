<?php

namespace App\Repository;

use App\Entity\User;
use App\QueryBuilder\UserQueryBuilder;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Persistence\ManagerRegistry;

class UserRepository extends AbstractRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly UserQueryBuilder $userQueryBuilder
    ) {
        parent::__construct($registry, User::class);
    }

    public function fetchAll(): ArrayCollection
    {
        return $this->userQueryBuilder
            ->createFetchAllQueryBuilder()
            ->getQuery()
            ->getResult();
    }

    public function findOneByIdentifier(string $identifier): ?User
    {
        return $this->userQueryBuilder
            ->createFindOneByIdentifierQueryBuilder($identifier)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findRelatedUsers(User $user): array
    {
        return $this->userQueryBuilder
            ->createFindRelatedUsersQueryBuilder($user)
            ->getQuery()
            ->getResult();
    }
}
