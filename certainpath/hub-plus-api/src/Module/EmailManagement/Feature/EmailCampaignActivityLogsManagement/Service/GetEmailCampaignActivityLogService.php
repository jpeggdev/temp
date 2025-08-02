<?php

declare(strict_types=1);

namespace App\Module\EmailManagement\Feature\EmailCampaignActivityLogsManagement\Service;

use App\Entity\EmailCampaignActivityLog;
use App\Module\EmailManagement\Feature\EmailCampaignActivityLogsManagement\DTO\Query\GetEmailCampaignActivityLogsDTO;
use App\Module\EmailManagement\Feature\EmailCampaignActivityLogsManagement\DTO\Response\GetEmailCampaignActivityLogsResponseDTO;
use App\Repository\EmailCampaignActivityLogRepository;

readonly class GetEmailCampaignActivityLogService
{
    public function __construct(
        private EmailCampaignActivityLogRepository $emailCampaignActivityLogRepository,
    ) {
    }

    /**
     * @throws \DateMalformedStringException
     */
    public function getEmailCampaignActivityLogs(GetEmailCampaignActivityLogsDTO $queryDto): array
    {
        $emailCampaignActivityLogs = $this->emailCampaignActivityLogRepository->findAllByDTO($queryDto);
        $emailCampaignActivityLogsTotalCount = $this->emailCampaignActivityLogRepository->getCountByDTO($queryDto);

        $emailCampaignActivityLogsDTOs = array_map(
            static fn (EmailCampaignActivityLog $log) => GetEmailCampaignActivityLogsResponseDTO::fromEntity(
                $log
            ),
            $emailCampaignActivityLogs->toArray()
        );

        return [
            'emailCampaignActivityLogs' => $emailCampaignActivityLogsDTOs,
            'totalCount' => $emailCampaignActivityLogsTotalCount,
        ];
    }
}
