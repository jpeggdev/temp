<?php

namespace App\Repository;

use App\DTO\Query\EmailCampaignStatuses\GetEmailCampaignStatusesDTO;
use App\Entity\EmailCampaignStatus;
use App\Exception\EmailCampaignStatusNotFoundException;
use App\QueryBuilder\EmailCampaignStatusQueryBuilder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EmailCampaignStatus>
 */
class EmailCampaignStatusRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly EmailCampaignStatusQueryBuilder $emailCampaignStatusQueryBuilder,
    ) {
        parent::__construct($registry, EmailCampaignStatus::class);
    }

    public function findOneByName(string $name): ?EmailCampaignStatus
    {
        return $this->emailCampaignStatusQueryBuilder
            ->createFindOneByNameQueryBuilder($name)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findOneByNameOrFail(string $name): EmailCampaignStatus
    {
        $result = $this->findOneByName($name);

        if (!$result) {
            throw new EmailCampaignStatusNotFoundException();
        }

        return $result;
    }

    /**
     * @return ArrayCollection<int, EmailCampaignStatus>
     */
    public function findAllByDTO(GetEmailCampaignStatusesDTO $queryDto): ArrayCollection
    {
        $result = $this->emailCampaignStatusQueryBuilder
            ->createFindAllByDTOQueryBuilder($queryDto)
            ->getQuery()
            ->getResult();

        return new ArrayCollection($result);
    }

    public function getCountByDTO(GetEmailCampaignStatusesDTO $queryDto): int
    {
        return $this->emailCampaignStatusQueryBuilder
            ->createGetCountByDTOQueryBuilder($queryDto)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
