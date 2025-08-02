<?php

namespace App\Repository;

use App\Entity\File;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<File>
 */
class FileRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, File::class);
    }

    public function findOneByUuid(string $uuid): ?File
    {
        return $this->findOneBy(['uuid' => $uuid]);
    }

    /**
     * Find multiple files by their UUIDs in a single query
     *
     * @param string[] $uuids
     * @return File[]
     */
    public function findByUuids(array $uuids): array
    {
        if (empty($uuids)) {
            return [];
        }

        return $this->createQueryBuilder('f')
            ->where('f.uuid IN (:uuids)')
            ->setParameter('uuids', $uuids)
            ->getQuery()
            ->getResult();
    }

    /**
     * Find files with the same MD5 hash.
     */
    public function findDuplicatesByHash(string $md5Hash, ?int $excludeFileId = null): array
    {
        $qb = $this->createQueryBuilder('f')
            ->where('f.md5Hash = :hash')
            ->setParameter('hash', $md5Hash);

        if (null !== $excludeFileId) {
            $qb->andWhere('f.id != :excludeId')
                ->setParameter('excludeId', $excludeFileId);
        }

        return $qb->getQuery()->getResult();
    }
}
