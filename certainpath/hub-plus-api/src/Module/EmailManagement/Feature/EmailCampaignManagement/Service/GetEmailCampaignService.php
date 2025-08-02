<?php

declare(strict_types=1);

namespace App\Module\EmailManagement\Feature\EmailCampaignManagement\Service;

use App\Entity\EmailCampaign;
use App\Module\EmailManagement\Feature\EmailCampaignManagement\DTO\Query\GetEmailCampaignsDTO;
use App\Module\EmailManagement\Feature\EmailCampaignManagement\DTO\Response\GetEmailCampaignResponseDTO;
use App\Repository\EmailCampaignRepository;

readonly class GetEmailCampaignService
{
    public function __construct(
        private EmailCampaignRepository $emailCampaignRepository,
    ) {
    }

    public function getEmailCampaign(int $id): GetEmailCampaignResponseDTO
    {
        $emailCampaign = $this->emailCampaignRepository->findOneByIdOrFail($id);

        return GetEmailCampaignResponseDTO::fromEntity($emailCampaign);
    }

    public function getEmailCampaigns(GetEmailCampaignsDTO $queryDto): array
    {
        $emailCampaigns = $this->emailCampaignRepository->findAllByDTO($queryDto);
        $emailCampaignsTotalCount = $this->emailCampaignRepository->getCountByDTO($queryDto);

        $emailCampaignDTOs = array_map(
            static fn (EmailCampaign $emailCampaign) => GetEmailCampaignResponseDTO::fromEntity($emailCampaign),
            $emailCampaigns->toArray()
        );

        return [
            'emailCampaigns' => $emailCampaignDTOs,
            'totalCount' => $emailCampaignsTotalCount,
        ];
    }
}
