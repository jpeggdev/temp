<?php

namespace App\Repository;

use App\Entity\Campaign;
use App\Exceptions\NotFoundException\CampaignNotFoundException;
use App\Services\PaginatorService;
use Doctrine\Common\Collections\ArrayCollection;
use App\QueryBuilder\CampaignQueryBuilder;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\Persistence\ManagerRegistry;

class CampaignRepository extends AbstractRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly PaginatorService $paginator,
        private readonly CampaignQueryBuilder $campaignQueryBuilder,
    ) {
        parent::__construct($registry, Campaign::class);
    }

    public function saveCampaign(Campaign $campaign): Campaign
    {
        /** @var Campaign $saved */
        $saved = $this->save($campaign);
        return $saved;
    }

    /**
     * @throws ORMException
     */
    public function refreshCampaign(Campaign $campaign): void
    {
        $this->getEntityManager()->refresh($campaign);
    }

    public function findOneById(int $id): ?Campaign
    {
        return $this->campaignQueryBuilder
            ->createFindByIdQueryBuilder($id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @throws CampaignNotFoundException
     */
    public function findOneByIdOrFail(int $id): Campaign
    {
        $result = $this->campaignQueryBuilder
            ->createFindByIdQueryBuilder($id)
            ->getQuery()
            ->getOneOrNullResult();

        if ($result === null) {
            throw new CampaignNotFoundException();
        }

        return $result;
    }

    public function findOneByName(string $name): ?Campaign
    {
        return $this->campaignQueryBuilder
            ->createFindByNameQueryBuilder($name)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function fetchAllByCompanyId(
        int $companyId,
        string $sortOrder = 'DESC'
    ): ArrayCollection {
        $result = $this->campaignQueryBuilder
            ->createFetchAllByCompanyIdQueryBuilder($companyId, $sortOrder)
            ->getQuery()
            ->getResult();

        return new ArrayCollection($result);
    }

    public function fetchAllByCompanyIdentifier(
        string $companyIdentifier,
        string $sortOrder = 'DESC',
    ): ArrayCollection {
        $result = $this->campaignQueryBuilder
            ->createFetchAllByCompanyIdentifierQueryBuilder($companyIdentifier, $sortOrder)
            ->getQuery()
            ->getResult();

        return new ArrayCollection($result);
    }

    public function fetchAllActive($sortOrder = 'DESC'): ArrayCollection
    {
        $result = $this->campaignQueryBuilder
            ->createFetchAllActiveQueryBuilder($sortOrder)
            ->getQuery()
            ->getResult();

        return new ArrayCollection($result);
    }

    public function fetchAllActiveByCompanyId(
        int $companyId,
        string $sortOrder = 'DESC'
    ): ArrayCollection {
        $result = $this->campaignQueryBuilder
            ->createFetchAllActiveByCompanyIdQueryBuilder($companyId, $sortOrder)
            ->getQuery()
            ->getResult();

        return new ArrayCollection($result);
    }

    public function paginateAllByCompanyId(
        int $id,
        int $page = 1,
        int $perPage = 10,
        string $sortOrder = 'DESC',
    ): array {
        $query = $this->campaignQueryBuilder
            ->createFetchAllByCompanyIdQueryBuilder($id, $sortOrder)
            ->getQuery();

        return $this->paginator->paginate($query, $page, $perPage);
    }

    public function paginateAllByCompanyIdentifierAndStatusId(
        string $identifier,
        ?int $campaignStatusId = null,
        int $page = 1,
        int $perPage = 10,
        string $sortOrder = 'DESC',
    ): array {
        $query = $this->campaignQueryBuilder
            ->createFetchAllByCompanyIdentifierAndStatusIdQueryBuilder($identifier, $campaignStatusId, $sortOrder)
            ->getQuery();

        return $this->paginator->paginate($query, $page, $perPage);
    }
}
