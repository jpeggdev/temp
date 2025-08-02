<?php

namespace App\Repository;

use App\Entity\Batch;
use App\Entity\BatchStatus;
use App\Exceptions\NotFoundException\BatchNotFoundException;
use App\QueryBuilder\BatchQueryBuilder;
use App\Services\PaginatorService;
use App\ValueObjects\BatchObject;
use Carbon\Carbon;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Exception;
use Doctrine\Persistence\ManagerRegistry;
use PDO;

class BatchRepository extends AbstractRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly PaginatorService $paginator,
        private readonly BatchQueryBuilder $batchQueryBuilder,
    ) {
        parent::__construct($registry, Batch::class);
    }

    public function saveBatch(Batch $batch): Batch
    {
        /** @var Batch $saved */
        $saved = $this->save($batch);
        return $saved;
    }

    /**
     * @throws Exception
     */
    public function saveBatchDBAL(BatchObject $batchObject): false|int|string
    {
        $connection = $this->getEntityManager()->getConnection();

        $params = [
            'name' => $batchObject->name,
            'campaign_id' => $batchObject->campaignId,
            'campaign_iteration_id' => $batchObject->campaignIterationId,
            'campaign_iteration_week_id' => $batchObject->campaignIterationWeekId,
            'batch_status_id' => $batchObject->batchStatusId,
            'updated_at' => $batchObject->updatedAt->format('Y-m-d H:i:s'),
            'created_at' => $batchObject->createdAt->format('Y-m-d H:i:s'),
        ];

        $types = [
            'name' => PDO::PARAM_STR,
            'campaign_id' => PDO::PARAM_INT,
            'campaign_iteration_id' => PDO::PARAM_INT,
            'campaign_iteration_week_id' => PDO::PARAM_INT,
            'batch_status_id' => PDO::PARAM_INT,
            'updated_at' => PDO::PARAM_STR,
            'created_at' => PDO::PARAM_STR,
        ];

        $connection->insert($batchObject->getTableName(), $params, $types);

        return $connection->lastInsertId();
    }

    public function findById(int $id): ?Batch
    {
        return $this->batchQueryBuilder
            ->createFindByIdQueryBuilder($id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @throws BatchNotFoundException
     */
    public function findByIdOrFail(int $id): Batch
    {
        $result = $this->batchQueryBuilder
            ->createFindByIdQueryBuilder($id)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$result) {
            throw new BatchNotFoundException();
        }

        return $result;
    }

    public function paginateAllByCampaignId(
        int $id,
        int $page = 1,
        int $perPage = 10,
        string $sortOrder = 'ASC'
    ): array {
        $query = $this->batchQueryBuilder
            ->createFetchAllByCampaignIdQueryBuilder($id, $sortOrder)
            ->getQuery();

        return $this->paginator->paginate($query, $page, $perPage);
    }

    public function paginateAllByCampaignIdAndStatusId(
        int $id,
        ?int $statusId = null,
        int $page = 1,
        int $perPage = 10,
        string $sortOrder = 'ASC'
    ): array {
        $query = $this->batchQueryBuilder
            ->createFetchAllByCampaignIdAndStatusIdQueryBuilder($id, $statusId, $sortOrder)
            ->getQuery();

        return $this->paginator->paginate($query, $page, $perPage);
    }

    /**
     * @return ArrayCollection<int, Batch>
     */
    public function fetchAllByCampaignIterationId(int $campaignIterationId): ArrayCollection
    {
        $result = $this->batchQueryBuilder
            ->createFetchAllByCampaignIterationIdQueryBuilder($campaignIterationId)
            ->getQuery()
            ->getResult();

        return new ArrayCollection($result);
    }

    /**
     * @return ArrayCollection<int, Batch>
     */
    public function findAllByWeekStartAndEndDate(
        Carbon $weekStartDate,
        Carbon $weekEndDate,
        string $sortOrder = 'ASC'
    ): ArrayCollection {
        $result = $this->batchQueryBuilder
            ->createFetchAllByWeekStartAndEndDateQueryBuilder($weekStartDate, $weekEndDate, $sortOrder)
            ->getQuery()
            ->getResult();

        return new ArrayCollection($result);
    }

    public function fetchAllByStatusAndWeekStartAndEndDatesQueryBuilder(
        BatchStatus $status,
        Carbon $weekStartDate,
        Carbon $weekEndDate
    ): ArrayCollection {
        $result = $this->batchQueryBuilder
            ->createFetchAllByStatusAndWeekStartAndEndDatesQueryBuilder($status, $weekStartDate, $weekEndDate)
            ->getQuery()
            ->getResult();

        return new ArrayCollection($result);
    }

    public function getBatchProspectsCount(int $batchId): int
    {
        return $this->batchQueryBuilder
            ->createGetBatchProspectsCountQueryBuilder($batchId)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
