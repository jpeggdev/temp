<?php

namespace App\Repository;

use App\Entity\CampaignIterationStatus;
use App\Exceptions\NotFoundException\CampaignIterationStatusNotFoundException;
use App\QueryBuilder\CampaignIterationStatusQueryBuilder;
use App\Services\PaginatorService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Persistence\ManagerRegistry;

class CampaignIterationStatusRepository extends AbstractRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly PaginatorService $paginator,
        private readonly CampaignIterationStatusQueryBuilder $campaignIterationStatusQueryBuilder,
    ) {
        parent::__construct($registry, CampaignIterationStatus::class);
    }

    public function saveCampaignIterationStatus(
        CampaignIterationStatus $campaignIterationStatus
    ): CampaignIterationStatus {
        /** @var CampaignIterationStatus $saved */
        $saved = $this->save($campaignIterationStatus);
        return $saved;
    }

    public function findById(int $id): ?CampaignIterationStatus
    {
        return $this->findOneBy(['id' => $id]);
    }

    public function findOneByName(string $name): ?CampaignIterationStatus
    {
        return $this->campaignIterationStatusQueryBuilder
            ->createFindOneByNameQueryBuilder($name)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @throws CampaignIterationStatusNotFoundException
     */
    public function findOneByNameOrFail(string $name): CampaignIterationStatus
    {
        $result = $this->findOneByName($name);

        if (!$result) {
            throw new CampaignIterationStatusNotFoundException();
        }

        return $result;
    }

    public function fetchAll(string $sortOrder = 'DESC'): ArrayCollection
    {
        $result = $this->campaignIterationStatusQueryBuilder
            ->createFetchAllQueryBuilder($sortOrder)
            ->getQuery()
            ->getResult();

        return new ArrayCollection($result);
    }

    public function paginateAll(int $page, int $perPage, string $sortOrder): array
    {
        $query = $this->campaignIterationStatusQueryBuilder
            ->createFetchAllQueryBuilder($sortOrder)
            ->getQuery();

        return $this->paginator->paginate($query, $page, $perPage);
    }
}
