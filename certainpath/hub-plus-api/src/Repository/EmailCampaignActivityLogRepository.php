<?php

namespace App\Repository;

use App\Entity\EmailCampaignActivityLog;
use App\Module\EmailManagement\Feature\EmailCampaignActivityLogsManagement\DTO\Query\BaseGetEmailCampaignActivityLogDTO;
use App\Module\EmailManagement\Feature\EmailCampaignActivityLogsManagement\DTO\Query\GetEmailCampaignActivityLogsDTO;
use App\Module\EmailManagement\Feature\EmailCampaignActivityLogsManagement\Exception\EmailCampaignActivityLogNotFoundException;
use App\QueryBuilder\EmailCampaignActivityLogQueryBuilder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EmailCampaignActivityLog>
 */
class EmailCampaignActivityLogRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly EmailCampaignActivityLogQueryBuilder $emailCampaignActivityLogQueryBuilder,
    ) {
        parent::__construct($registry, EmailCampaignActivityLog::class);
    }

    public function findOneByMessageId(string $messageId): ?EmailCampaignActivityLog
    {
        return $this->emailCampaignActivityLogQueryBuilder
            ->createFindOneByMessageIdQueryBuilder($messageId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findOneByMessageIdOrFail(string $messageId): EmailCampaignActivityLog
    {
        $result = $this->findOneByMessageId($messageId);

        if (!$result) {
            throw new EmailCampaignActivityLogNotFoundException();
        }

        return $result;
    }

    /**
     * @return ArrayCollection<int, EmailCampaignActivityLog>
     */
    public function findAllByDTO(GetEmailCampaignActivityLogsDTO $queryDto): ArrayCollection
    {
        $result = $this->emailCampaignActivityLogQueryBuilder
            ->createFindAllByDTOQueryBuilder($queryDto)
            ->getQuery()
            ->getResult();

        return new ArrayCollection($result);
    }

    /**
     * @throws \DateMalformedStringException
     */
    public function getCountByDTO(BaseGetEmailCampaignActivityLogDTO $queryDto): int
    {
        return $this->emailCampaignActivityLogQueryBuilder
            ->createGetCountByDTO($queryDto)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
