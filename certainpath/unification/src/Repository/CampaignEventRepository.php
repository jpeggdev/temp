<?php

namespace App\Repository;

use App\DTO\Request\Campaign\CreateCampaignDTO;
use App\Entity\CampaignEvent;
use App\Exceptions\NotFoundException\CampaignEventNotFoundException;
use App\QueryBuilder\CampaignEventQueryBuilder;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Persistence\ManagerRegistry;

class CampaignEventRepository extends AbstractRepository
{
    public function __construct(
        private readonly ManagerRegistry $registry,
        private readonly CampaignEventQueryBuilder $campaignEventQueryBuilder,
    ) {
        parent::__construct($registry, CampaignEvent::class);
    }

    public function addEvent(CampaignEvent $eventToSave): void
    {
        if (!$this->getEntityManager()->isOpen()) {
            $this->registry->resetManager();
        }
        $this->save($eventToSave);
    }

    public function findAllByCampaignId(int $campaignId): ArrayCollection
    {
        $result = $this->campaignEventQueryBuilder
            ->createFindAllByCampaignIdQueryBuilder($campaignId)
            ->getQuery()
            ->getResult();

        return new ArrayCollection($result);
    }

    public function findOneById(?int $getId): ?CampaignEvent
    {
        return $this->campaignEventQueryBuilder
            ->createFindOneByIdQueryBuilder($getId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @throws CampaignEventNotFoundException
     */
    public function findOneByIdOrFail(int $id): CampaignEvent
    {
        $result = $this->findOneById($id);

        if ($result === null) {
            $message = sprintf('Campaign event with id %d was not found.', $id);
            throw new CampaignEventNotFoundException($message);
        }

        return $result;
    }

    public function findLastByCampaignId(int $id): ?CampaignEvent
    {
        return $this->campaignEventQueryBuilder
            ->createFindAllByCampaignIdQueryBuilder($id)
            ->orderBy('ce.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @throws CampaignEventNotFoundException
     */
    public function findLastByCampaignIdOrFail(int $id): CampaignEvent
    {
        $result = $this->findLastByCampaignId($id);

        if ($result === null) {
            $message = sprintf('Campaign with id %d has no associated events.', $id);
            throw new CampaignEventNotFoundException($message);
        }

        return $result;
    }

    public function findLastByCampaignIdentified(string $campaignIdentifier): ?CampaignEvent
    {
        return $this->campaignEventQueryBuilder
            ->createFetchAllByCampaignIdentifierBuilder($campaignIdentifier)
            ->orderBy('ce.createdAt', 'DESC')
            ->addOrderBy('ce.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
