<?php

namespace App\Repository;

use App\Entity\CampaignStatus;
use App\Exceptions\NotFoundException\CampaignStatusNotFoundException;
use App\QueryBuilder\CampaignStatusQueryBuilder;
use App\Services\PaginatorService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Persistence\ManagerRegistry;

class CampaignStatusRepository extends AbstractRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly PaginatorService $paginator,
        private readonly CampaignStatusQueryBuilder $campaignStatusQueryBuilder,
    ) {
        parent::__construct($registry, CampaignStatus::class);
    }

    public function saveCampaignStatus(CampaignStatus $campaign): CampaignStatus
    {
        /** @var CampaignStatus $saved */
        $saved = $this->save($campaign);
        return $saved;
    }

    public function findById(int $id): ?CampaignStatus
    {
        return $this->campaignStatusQueryBuilder
            ->createFindByIdQueryBuilder($id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findOneByName(string $name): ?CampaignStatus
    {
        return $this->campaignStatusQueryBuilder
            ->createFindByNameQueryBuilder($name)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @throws CampaignStatusNotFoundException
     */
    public function findOneByNameOrFail(string $name): CampaignStatus
    {
        $result = $this->findOneByName($name);

        if (!$result) {
            throw new CampaignStatusNotFoundException();
        }

        return $result;
    }

    public function fetchAll(): ArrayCollection
    {
        $result = $this->campaignStatusQueryBuilder
            ->createFetchAllQueryBuilder()
            ->getQuery()
            ->getResult();

        return new ArrayCollection($result);
    }

    public function paginateAll(
        int $page = 1,
        int $perPage = 10,
        string $sortOrder = 'DESC'
    ): array {
        $query = $this->campaignStatusQueryBuilder
            ->createFetchAllQueryBuilder($sortOrder)
            ->getQuery();

        return $this->paginator->paginate($query, $page, $perPage);
    }
}
