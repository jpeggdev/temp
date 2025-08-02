<?php

declare(strict_types=1);

namespace App\Module\EmailManagement\Feature\EmailCampaignManagement\Service;

use App\Module\EmailManagement\Feature\EmailCampaignManagement\DTO\Query\GetEmailCampaignRecipientCountDTO;
use App\Repository\EventEnrollmentRepository;
use App\Repository\EventSession\EventSessionRepository;

readonly class GetEmailCampaignRecipientCountService
{
    public function __construct(
        private EventSessionRepository $eventSessionRepository,
        private EventEnrollmentRepository $eventEnrollmentRepository,
    ) {
    }

    public function getRecipientCount(GetEmailCampaignRecipientCountDTO $queryDTO): array
    {
        $eventSession = $this->eventSessionRepository->findOneByIdOrFail($queryDTO->eventSessionId);
        $recipientCount = $this->eventEnrollmentRepository->countEnrollmentsForSession($eventSession);

        return [
            'count' => $recipientCount,
        ];
    }
}
