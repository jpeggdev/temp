<?php

declare(strict_types=1);

namespace App\Service\EmailCampaignStatus;

use App\DTO\Query\EmailCampaignStatuses\GetEmailCampaignStatusesDTO;
use App\DTO\Response\GetEmailCampaignStatusesResponseDTO;
use App\Entity\EmailCampaignStatus;
use App\Repository\EmailCampaignStatusRepository;

readonly class GetEmailCampaignStatusService
{
    public function __construct(
        private EmailCampaignStatusRepository $emailCampaignStatusRepository,
    ) {
    }

    public function getEmailCampaignStatuses(GetEmailCampaignStatusesDTO $queryDto): array
    {
        $emailCampaignStatuses = $this->emailCampaignStatusRepository->findAllByDTO($queryDto);
        $emailCampaignStatusesTotalCount = $this->emailCampaignStatusRepository->getCountByDTO($queryDto);

        $emailCampaignStatusDTOs = array_map(
            static fn (EmailCampaignStatus $emailCampaignStatus) => GetEmailCampaignStatusesResponseDTO::fromEntity(
                $emailCampaignStatus
            ),
            $emailCampaignStatuses->toArray()
        );

        return [
            'emailCampaignStatuses' => $emailCampaignStatusDTOs,
            'totalCount' => $emailCampaignStatusesTotalCount,
        ];
    }
}
