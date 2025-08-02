<?php

namespace App\Repository;

use App\Entity\BatchStatus;
use App\Exceptions\NotFoundException\BatchStatusNotFoundException;
use App\QueryBuilder\BatchStatusQueryBuilder;
use App\Services\PaginatorService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Persistence\ManagerRegistry;

class BatchStatusRepository extends AbstractRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly PaginatorService $paginator,
        private readonly BatchStatusQueryBuilder $batchStatusQueryBuilder,
    ) {
        parent::__construct($registry, BatchStatus::class);
    }

    public function saveBatchStatus(BatchStatus $batchStatus): BatchStatus
    {
        /** @var BatchStatus $saved */
        $saved = $this->save($batchStatus);
        return $saved;
    }

    public function findOneById(int $id): ?BatchStatus
    {
        return $this->batchStatusQueryBuilder
            ->createFindByIdQueryBuilder($id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findOneByName(string $name): ?BatchStatus
    {
        return $this->batchStatusQueryBuilder
            ->createFindByNameQueryBuilder($name)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @throws BatchStatusNotFoundException
     */
    public function findOneByNameOrFail($STATUS_NEW): BatchStatus
    {
        $result = $this->findOneByName($STATUS_NEW);

        if (!$result) {
            throw new BatchStatusNotFoundException();
        }

        return $result;
    }

    public function fetchAll(string $sortOrder = 'DESC'): ArrayCollection
    {
        $result = $this->batchStatusQueryBuilder
            ->createFetchAllQueryBuilder($sortOrder)
            ->getQuery()
            ->getResult();

        return new ArrayCollection($result);
    }

    public function paginateAll(
        int $page = 1,
        int $perPage = 10,
        string $sortOrder = 'DESC'
    ): array {
        $query = $this->batchStatusQueryBuilder
            ->createFetchAllQueryBuilder($sortOrder)
            ->getQuery();

        return $this->paginator->paginate($query, $page, $perPage);
    }
}
