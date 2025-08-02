<?php

namespace App\Repository;

use App\Entity\CampaignIterationWeek;
use App\Exceptions\NotFoundException\CampaignIterationWeekNotFoundException;
use App\QueryBuilder\CampaignIterationWeekQueryBuilder;
use App\ValueObjects\CampaignIterationWeekObject;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Exception;
use Doctrine\Persistence\ManagerRegistry;
use PDO;

class CampaignIterationWeekRepository extends AbstractRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly CampaignIterationWeekQueryBuilder $queryBuilder,
    ) {
        parent::__construct($registry, CampaignIterationWeek::class);
    }

    public function saveCampaignIterationWeek(
        CampaignIterationWeek $campaignIterationWeek
    ): CampaignIterationWeek {
        /** @var CampaignIterationWeek $saved */
        $saved = $this->save($campaignIterationWeek);
        return $saved;
    }

    /**
     * @throws Exception
     */
    public function saveCampaignIterationWeekDBAL(CampaignIterationWeekObject $campaignIterationWeekObject): int
    {
        $connection = $this->getEntityManager()->getConnection();

        $params = [
            'campaign_iteration_id' => $campaignIterationWeekObject->campaignIterationId,
            'week_number' => $campaignIterationWeekObject->weekNumber,
            'is_mailing_drop_week' => $campaignIterationWeekObject->isMailingDropWeek,
            'start_date' => $campaignIterationWeekObject->startDate,
            'end_date' => $campaignIterationWeekObject->endDate,
            'created_at' => $campaignIterationWeekObject->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $campaignIterationWeekObject->updatedAt->format('Y-m-d H:i:s'),
        ];

        $types = [
            'campaign_iteration_id' => PDO::PARAM_INT,
            'week_number' => PDO::PARAM_INT,
            'is_mailing_drop_week' => PDO::PARAM_BOOL,
            'start_date' => PDO::PARAM_STR,
            'end_date' => PDO::PARAM_STR,
            'created_at' => PDO::PARAM_STR,
            'updated_at' => PDO::PARAM_STR,
        ];

        $connection->insert($campaignIterationWeekObject->getTableName(), $params, $types);

        return $connection->lastInsertId();
    }

    /**
     * @param ArrayCollection<int, CampaignIterationWeekObject> $campaignIterationWeeksObjects
     * @throws Exception
     */
    public function bulkInsertCampaignIterationWeeks(ArrayCollection $campaignIterationWeeksObjects): void
    {
        if ($campaignIterationWeeksObjects->isEmpty()) {
            return;
        }

        [$sql, $params] = $this->queryBuilder->createBulkInsertCampaignIterationWeeksSQLQuery(
            $campaignIterationWeeksObjects
        );

        $connection = $this->getEntityManager()->getConnection();
        $connection->executeStatement($sql, $params);
    }

    public function findOneById(int $id): ?CampaignIterationWeek
    {
        return $this->queryBuilder
            ->createFindByIdQueryBuilder($id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @throws CampaignIterationWeekNotFoundException
     */
    public function findOneByIdOrFail(int $id): CampaignIterationWeek
    {
        $result = $this->findOneById($id);
        if ($result === null) {
            throw new CampaignIterationWeekNotFoundException();
        }

        return $result;
    }

    public function findAllByCampaignIterationId(
        int $campaignIterationId,
        string $sortOrder = 'ASC'
    ): ArrayCollection {
        $result = $this->queryBuilder
            ->createFindAllByCampaignIterationIdQueryBuilder($campaignIterationId, $sortOrder)
            ->getQuery()
            ->getResult();

        return new ArrayCollection($result);
    }

    public function findLatestByCampaignIterationId(int $campaignIterationId): ?CampaignIterationWeek
    {
        return $this->queryBuilder
            ->createFindAllByCampaignIterationIdQueryBuilder($campaignIterationId, 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
