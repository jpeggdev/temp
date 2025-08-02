<?php

declare(strict_types=1);

namespace App\Module\EmailManagement\Feature\EmailCampaignActivityLogsManagement\Service;

use App\Module\EmailManagement\Feature\EmailCampaignActivityLogsManagement\DTO\Query\GetEmailCampaignActivityLogsMetadataDTO;
use App\Repository\EmailCampaignActivityLogRepository;

readonly class GetEmailCampaignActivityLogsMetadataService
{
    public function __construct(
        private EmailCampaignActivityLogRepository $emailCampaignActivityLogRepository,
    ) {
    }

    /**
     * @throws \DateMalformedStringException
     */
    public function getMetadata(GetEmailCampaignActivityLogsMetadataDTO $queryDTO): array
    {
        $emailEventSentCount = $this->getEmailEventSentCount($queryDTO);
        $emailEventDeliveredCount = $this->getEmailEventDeliveredCount($queryDTO);
        $emailEventOpenedCount = $this->getEmailEventOpenedCount($queryDTO);
        $emailEventClickedCount = $this->getClickedCount($queryDTO);
        $emailEventBouncedCount = $this->getBouncedCount($queryDTO);

        $emailEventDeliveredRate = $this->calculateRate($emailEventDeliveredCount, $emailEventSentCount);
        $emailEventOpenedRate = $this->calculateRate($emailEventOpenedCount, $emailEventSentCount);
        $emailEventClickedRate = $this->calculateRate($emailEventClickedCount, $emailEventSentCount);
        $emailEventBouncedRate = $this->calculateRate($emailEventBouncedCount, $emailEventSentCount);

        return [
            'emailEventCount' => [
                'delivered' => $emailEventDeliveredCount,
                'opened' => $emailEventOpenedCount,
                'clicked' => $emailEventClickedCount,
                'failed' => $emailEventBouncedCount,
            ],
            'emailEventRate' => [
                'delivered' => $emailEventDeliveredRate,
                'opened' => $emailEventOpenedRate,
                'clicked' => $emailEventClickedRate,
                'failed' => $emailEventBouncedRate,
            ],
        ];
    }

    /**
     * @throws \DateMalformedStringException
     */
    private function getEmailEventSentCount(GetEmailCampaignActivityLogsMetadataDTO $dto): int
    {
        $getSentCount = new GetEmailCampaignActivityLogsMetadataDTO(
            isSent: true,
            emailEventPeriodFilter: $dto->emailEventPeriodFilter,
        );

        return $this->emailCampaignActivityLogRepository->getCountByDTO($getSentCount);
    }

    /**
     * @throws \DateMalformedStringException
     */
    private function getEmailEventDeliveredCount(GetEmailCampaignActivityLogsMetadataDTO $dto): int
    {
        $getEmailEventDeliveredCount = new GetEmailCampaignActivityLogsMetadataDTO(
            isDelivered: true,
            emailEventPeriodFilter: $dto->emailEventPeriodFilter,
        );

        return $this->emailCampaignActivityLogRepository->getCountByDTO($getEmailEventDeliveredCount);
    }

    /**
     * @throws \DateMalformedStringException
     */
    private function getEmailEventOpenedCount(GetEmailCampaignActivityLogsMetadataDTO $dto): int
    {
        $getOpenedCount = new GetEmailCampaignActivityLogsMetadataDTO(
            isOpened: true,
            emailEventPeriodFilter: $dto->emailEventPeriodFilter,
        );

        return $this->emailCampaignActivityLogRepository->getCountByDTO($getOpenedCount);
    }

    /**
     * @throws \DateMalformedStringException
     */
    private function getClickedCount(GetEmailCampaignActivityLogsMetadataDTO $dto): int
    {
        $getClickedCount = new GetEmailCampaignActivityLogsMetadataDTO(
            isClicked: true,
            emailEventPeriodFilter: $dto->emailEventPeriodFilter,
        );

        return $this->emailCampaignActivityLogRepository->getCountByDTO($getClickedCount);
    }

    /**
     * @throws \DateMalformedStringException
     */
    private function getBouncedCount(GetEmailCampaignActivityLogsMetadataDTO $dto): int
    {
        $getBouncedCount = new GetEmailCampaignActivityLogsMetadataDTO(
            isBounced: true,
            emailEventPeriodFilter: $dto->emailEventPeriodFilter,
        );

        return $this->emailCampaignActivityLogRepository->getCountByDTO($getBouncedCount);
    }

    private function calculateRate(
        int $numerator,
        int $denominator,
    ): float {
        if (0 === $denominator) {
            return 0;
        }

        return (float) number_format(($numerator / $denominator) * 100, 1);
    }
}
