<?php

namespace App\Repository;

use App\Entity\CampaignIteration;
use App\Exceptions\NotFoundException\CampaignIterationNotFoundException;
use App\QueryBuilder\CampaignIterationQueryBuilder;
use App\ValueObjects\CampaignIterationObject;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\Persistence\ManagerRegistry;

class CampaignIterationRepository extends AbstractRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly CampaignIterationQueryBuilder $campaignIterationQueryBuilder
    ) {
        parent::__construct($registry, CampaignIteration::class);
    }

    public function saveCampaignIteration(CampaignIteration $campaignIteration): CampaignIteration
    {
        /** @var CampaignIteration $saved */
        $saved = $this->save($campaignIteration);
        return $saved;
    }

    /**
     * @throws Exception
     */
    public function saveCampaignIterationDBAL(CampaignIterationObject $campaignIterationObject): false|int|string
    {
        $connection = $this->getEntityManager()->getConnection();

        $params = [
            'campaign_id' => $campaignIterationObject->campaignId,
            'iteration_number' => $campaignIterationObject->iterationNumber,
            'campaign_iteration_status_id' => $campaignIterationObject->campaignIterationStatusId,
            'start_date' => $campaignIterationObject->startDate,
            'end_date' => $campaignIterationObject->endDate,
            'updated_at' => $campaignIterationObject->updatedAt->format('Y-m-d H:i:s'),
            'created_at' => $campaignIterationObject->createdAt->format('Y-m-d H:i:s'),
        ];

        $types = [
            'campaign_id' => \PDO::PARAM_INT,
            'iteration_number' => \PDO::PARAM_INT,
            'campaign_iteration_status_id' => \PDO::PARAM_INT,
            'start_date' => \PDO::PARAM_STR,
            'end_date' => \PDO::PARAM_STR,
            'updated_at' => \PDO::PARAM_STR,
            'created_at' => \PDO::PARAM_STR,
        ];

        $connection->insert($campaignIterationObject->getTableName(), $params, $types);
        return $connection->lastInsertId();
    }

    /**
     * @throws ORMException
     */
    public function refreshCampaignIteration(CampaignIteration $campaignIteration): void
    {
        $this->getEntityManager()->refresh($campaignIteration);
    }

    public function findOneById(int $id): ?CampaignIteration
    {
        return $this->campaignIterationQueryBuilder
            ->createFindByIdQueryBuilder($id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findAllByCampaignId(
        int $campaignId,
        string $sortOrder = 'ASC'
    ): ArrayCollection {
        return new ArrayCollection($this->campaignIterationQueryBuilder
            ->createFindAllByCampaignIdQueryBuilder($campaignId, $sortOrder)
            ->getQuery()
            ->getResult());
    }

    public function findAllByCampaignIdAndStatus(
        int $campaignId,
        array $status
    ): ArrayCollection {
        return new ArrayCollection($this->campaignIterationQueryBuilder
            ->createFindAllByCampaignIdAndStatusesQueryBuilder($campaignId, $status)
            ->getQuery()
            ->getResult());
    }

    public function findNextActiveByCampaignId(
        int $campaignId,
        DateTime $iterationStartDate = null,
        string $sortOrder = 'ASC'
    ): ?CampaignIteration {
        return $this->campaignIterationQueryBuilder
            ->createFindNextActiveByCampaignIdQueryBuilder($campaignId, $iterationStartDate, $sortOrder)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findCurrentActiveByCampaignId(
        int $campaignId,
        string $sortOrder = 'ASC'
    ): ?CampaignIteration {
        return $this->campaignIterationQueryBuilder
            ->createFindCurrentActiveByCampaignIdQueryBuilder($campaignId, $sortOrder)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findNextPendingByCampaignId(
        ?int $campaignId,
        DateTime $iterationStartDate = null,
        string $sortOrder = 'ASC'
    ): ?CampaignIteration {
        return $this->campaignIterationQueryBuilder
            ->createFindNextPendingByCampaignIdQueryBuilder($campaignId, $iterationStartDate, $sortOrder)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findCampaignIterationsByCampaignIdAndDateBeforeOrEqual(
        ?int $campaignId,
        DateTime $iterationStartDate,
        string $sortOrder = 'ASC'
    ): ArrayCollection {
        $result = $this->campaignIterationQueryBuilder
            ->createFindCampaignIterationsByCampaignIdAndDateBeforeOrEqual(
                $campaignId,
                $iterationStartDate,
                $sortOrder
            )
            ->getQuery()
            ->getResult();

        return new ArrayCollection($result);
    }

    public function findLatestByCampaignId(int $campaignId): ?CampaignIteration
    {
        return $this->campaignIterationQueryBuilder
            ->createFindAllByCampaignIdQueryBuilder($campaignId, 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @throws CampaignIterationNotFoundException
     */
    public function findLatestByCampaignIdOrFail(int $campaignId): CampaignIteration
    {
        $result = $this->findLatestByCampaignId($campaignId);

        if (!$result) {
            $message = sprintf('Campaign with id %d has no associated iterations.', $campaignId);
            throw new CampaignIterationNotFoundException($message);
        }

        return $result;
    }
}
