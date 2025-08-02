<?php

namespace App\QueryBuilder;

use App\Entity\EmailCampaignActivityLog;
use App\Module\EmailManagement\Feature\EmailCampaignActivityLogsManagement\DTO\Query\BaseGetEmailCampaignActivityLogDTO;
use App\Module\EmailManagement\Feature\EmailCampaignActivityLogsManagement\DTO\Query\GetEmailCampaignActivityLogsDTO;
use App\QueryBuilder\Filter\IdFilterTrait;
use Doctrine\ORM\QueryBuilder;

readonly class EmailCampaignActivityLogQueryBuilder extends AbstractQueryBuilder
{
    use IdFilterTrait;

    public const string ALIAS = 'ecal';

    protected function createBaseQueryBuilder(): QueryBuilder
    {
        return $this->em->createQueryBuilder()
            ->select(self::ALIAS)
            ->from(EmailCampaignActivityLog::class, self::ALIAS);
    }

    protected function createBaseCountQueryBuilder(): QueryBuilder
    {
        return $this->em->createQueryBuilder()
            ->select('COUNT('.self::ALIAS.'.id)')
            ->from(EmailCampaignActivityLog::class, self::ALIAS);
    }

    public function createFindOneByIdQueryBuilder(int $id): QueryBuilder
    {
        $queryBuilder = $this->createBaseQueryBuilder();

        return $this->applyIdFilter($queryBuilder, self::ALIAS, $id);
    }

    public function createFindOneByMessageIdQueryBuilder(string $messageId): QueryBuilder
    {
        $queryBuilder = $this->createBaseQueryBuilder();

        return $this->applyMessageIdQueryBuilder($queryBuilder, $messageId);
    }

    /**
     * @throws \DateMalformedStringException
     */
    public function createFindAllByDTOQueryBuilder(GetEmailCampaignActivityLogsDTO $queryDTO): QueryBuilder
    {
        $queryBuilder = $this->createBaseQueryBuilder();
        $queryBuilder = $this->applyFilters($queryBuilder, $queryDTO);

        $queryBuilder->setMaxResults($queryDTO->pageSize);
        $queryBuilder->setFirstResult(($queryDTO->page - 1) * $queryDTO->pageSize);
        $queryBuilder->orderBy(self::ALIAS.'.'.$queryDTO->sortBy, $queryDTO->sortOrder);

        return $queryBuilder;
    }

    /**
     * @throws \DateMalformedStringException
     */
    public function createGetCountByDTO(BaseGetEmailCampaignActivityLogDTO $queryDto): QueryBuilder
    {
        $queryBuilder = $this->createBaseCountQueryBuilder();

        return $this->applyFilters($queryBuilder, $queryDto);
    }

    private function applyMessageIdQueryBuilder(
        QueryBuilder $queryBuilder,
        string $messageId,
    ): QueryBuilder {
        return $queryBuilder
            ->andWhere(self::ALIAS.'.messageId = :messageId')
            ->setParameter('messageId', $messageId);
    }

    private function applySearchTermFilter(
        QueryBuilder $queryBuilder,
        ?string $searchTerm,
    ): QueryBuilder {
        $queryBuilder
            ->andWhere(
                $queryBuilder->expr()->orX(
                    'LOWER('.self::ALIAS.'.email) LIKE LOWER(:searchTerm)',
                    'LOWER(ecal.subject) LIKE LOWER(:searchTerm)'
                )
            )
            ->setParameter('searchTerm', '%'.strtolower($searchTerm).'%');

        return $queryBuilder;
    }

    /**
     * @throws \DateMalformedStringException
     */
    private function applyFilters(
        QueryBuilder $queryBuilder,
        BaseGetEmailCampaignActivityLogDTO $queryDTO,
    ): QueryBuilder {
        if (property_exists($queryDTO, 'searchTerm') && $queryDTO->searchTerm) {
            $queryBuilder = $this->applySearchTermFilter($queryBuilder, $queryDTO->searchTerm);
        }

        if (property_exists($queryDTO, 'emailEventPeriodFilter') && $queryDTO->emailEventPeriodFilter) {
            $queryBuilder = $this->applyEventSentAtPeriodFilter($queryBuilder, $queryDTO->emailEventPeriodFilter);
        }

        if (property_exists($queryDTO, 'isSent') && null !== $queryDTO->isSent) {
            $queryBuilder = $this->applyIsSentFilter($queryBuilder, $queryDTO->isSent);
        }

        if (property_exists($queryDTO, 'isDelivered') && null !== $queryDTO->isDelivered) {
            $queryBuilder = $this->applyIsDeliveredFilter($queryBuilder, $queryDTO->isDelivered);
        }

        if (property_exists($queryDTO, 'isOpened') && null !== $queryDTO->isOpened) {
            $queryBuilder = $this->applyIsOpenedFilter($queryBuilder, $queryDTO->isOpened);
        }

        if (property_exists($queryDTO, 'isClicked') && null !== $queryDTO->isClicked) {
            $queryBuilder = $this->applyIsClickedFilter($queryBuilder, $queryDTO->isClicked);
        }

        if (property_exists($queryDTO, 'isBounced') && null !== $queryDTO->isBounced) {
            $queryBuilder = $this->applyIsBouncedFilter($queryBuilder, $queryDTO->isBounced);
        }

        return $queryBuilder;
    }

    private function applyIsSentFilter(
        QueryBuilder $queryBuilder,
        bool $isSent = true,
    ): QueryBuilder {
        $queryBuilder
            ->andWhere(self::ALIAS.'.isSent = :isSent')
            ->setParameter('isSent', $isSent);

        return $queryBuilder;
    }

    private function applyIsDeliveredFilter(
        QueryBuilder $queryBuilder,
        bool $isDelivered = true,
    ): QueryBuilder {
        $queryBuilder
            ->andWhere(self::ALIAS.'.isDelivered = :isDelivered')
            ->setParameter('isDelivered', $isDelivered);

        return $queryBuilder;
    }

    private function applyIsOpenedFilter(
        QueryBuilder $queryBuilder,
        bool $isOpened,
    ): QueryBuilder {
        $queryBuilder
            ->andWhere(self::ALIAS.'.isOpened = :isOpened')
            ->setParameter('isOpened', $isOpened);

        return $queryBuilder;
    }

    private function applyIsClickedFilter(
        QueryBuilder $queryBuilder,
        bool $isClicked,
    ): QueryBuilder {
        $queryBuilder
            ->andWhere(self::ALIAS.'.isClicked = :isClicked')
            ->setParameter('isClicked', $isClicked);

        return $queryBuilder;
    }

    private function applyIsBouncedFilter(
        QueryBuilder $queryBuilder,
        bool $isBounced,
    ): QueryBuilder {
        $queryBuilder
            ->andWhere(self::ALIAS.'.isBounced = :isBounced')
            ->setParameter('isBounced', $isBounced);

        return $queryBuilder;
    }

    /**
     * @throws \DateMalformedStringException
     */
    private function applyEventSentAtPeriodFilter(
        QueryBuilder $queryBuilder,
        string $eventSentAtEndDate,
    ): QueryBuilder {
        $endDate = (new \DateTime())->setTime(0, 0);

        switch ($eventSentAtEndDate) {
            case BaseGetEmailCampaignActivityLogDTO::DATE_RANGE_TODAY:
                break;
            case BaseGetEmailCampaignActivityLogDTO::DATE_RANGE_LAST_7_DAYS:
                $endDate->modify('-6 days');
                break;
            case BaseGetEmailCampaignActivityLogDTO::DATE_RANGE_LAST_30_DAYS:
                $endDate->modify('-29 days');
                break;
            case BaseGetEmailCampaignActivityLogDTO::DATE_RANGE_LAST_90_DAYS:
                $endDate->modify('-89 days');
                break;
            default:
                throw new \InvalidArgumentException("Invalid date range filter: $eventSentAtEndDate");
        }

        $queryBuilder
            ->andWhere(self::ALIAS.'.eventSentAt >= :eventSentAtEndDate')
            ->setParameter('eventSentAtEndDate', $endDate);

        return $queryBuilder;
    }
}
