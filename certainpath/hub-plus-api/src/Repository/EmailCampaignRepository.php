<?php

namespace App\Repository;

use App\Entity\EmailCampaign;
use App\Module\EmailManagement\Feature\EmailCampaignManagement\DTO\Query\GetEmailCampaignsDTO;
use App\Module\EmailManagement\Feature\EmailCampaignManagement\Exception\EmailCampaignNotFoundException;
use App\QueryBuilder\EmailCampaignQueryBuilder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EmailCampaign>
 */
class EmailCampaignRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly EmailCampaignQueryBuilder $emailCampaignQueryBuilder,
    ) {
        parent::__construct($registry, EmailCampaign::class);
    }

    public function save(EmailCampaign $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findOneById(int $id): ?EmailCampaign
    {
        return $this->emailCampaignQueryBuilder
            ->createFindOneByIdQueryBuilder($id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findOneByIdOrFail(int $id): EmailCampaign
    {
        $result = $this->findOneById($id);

        if (!$result) {
            throw new EmailCampaignNotFoundException();
        }

        return $result;
    }

    /**
     * @return ArrayCollection<int, EmailCampaign>
     */
    public function findAllByDTO(GetEmailCampaignsDTO $queryDto): ArrayCollection
    {
        $result = $this->emailCampaignQueryBuilder
            ->createFindAllByDTOQueryBuilder($queryDto)
            ->getQuery()
            ->getResult();

        return new ArrayCollection($result);
    }

    public function getCountByDTO(GetEmailCampaignsDTO $queryDto): int
    {
        return $this->emailCampaignQueryBuilder
            ->createGetCountByDTOQueryBuilder($queryDto)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
