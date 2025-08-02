<?php

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\Exception\ORMException;

abstract class AbstractRepository extends ServiceEntityRepository
{
    public const RESULTS_PER_PAGE = 25;

    /**
     * @throws ORMException
     */
    public function refresh(object $entity): void
    {
        $this->getEntityManager()->refresh($entity);
    }

    public function persist(object $entity): void
    {
        $this->getEntityManager()->persist($entity);
    }

    public function flush(): void
    {
        $this->getEntityManager()->flush();
    }

    public function save(object $entity): object
    {
        $this->persist($entity);
        $this->flush();
        return $entity;
    }

    public function remove(object $entity): void
    {
        $this->getEntityManager()->remove($entity);
        $this->flush();
    }

    public function getRepoConnection(): Connection
    {
        return $this->getEntityManager()->getConnection();
    }
}
