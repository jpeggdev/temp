<?php

namespace App\Module\EmailManagement\Feature\EmailCampaignManagement\Service;

use App\Entity\EmailCampaign;
use App\Exception\UnsupportedSendOptionException;
use App\Module\EmailManagement\Feature\EmailCampaignManagement\DTO\Request\CreateUpdateEmailCampaignDTO;
use App\Module\EmailManagement\Feature\EmailCampaignManagement\Trait\ResolveEmailCampaignStatusTrait;
use App\Repository\EmailCampaignRepository;
use App\Repository\EmailCampaignStatusRepository;
use App\Repository\EmailTemplateRepository;
use App\Repository\EventRepository\EventRepository;
use App\Repository\EventSession\EventSessionRepository;

readonly class UpdateEmailCampaignService
{
    use ResolveEmailCampaignStatusTrait;

    public function __construct(
        private EventRepository $eventRepository,
        private EventSessionRepository $eventSessionRepository,
        private EmailTemplateRepository $emailTemplateRepository,
        private EmailCampaignRepository $emailCampaignRepository,
        private EmailCampaignStatusRepository $emailCampaignStatusRepository,
    ) {
    }

    /**
     * @throws UnsupportedSendOptionException
     */
    public function updateCampaign(
        int $id,
        CreateUpdateEmailCampaignDTO $requestDTO,
    ): EmailCampaign {
        $emailCampaign = $this->emailCampaignRepository->findOneByIdOrFail(
            $id
        );
        $event = $this->eventRepository->findOneByIdOrFail(
            $requestDTO->eventId
        );
        $eventSession = $this->eventSessionRepository->findOneByIdOrFail(
            $requestDTO->sessionId
        );
        $emailTemplate = $this->emailTemplateRepository->findOneByIdOrFail(
            $requestDTO->emailTemplateId
        );
        $emailCampaignStatus = $this->resolveEmailCampaignStatus(
            $requestDTO->sendOption
        );

        $emailCampaign
            ->setCampaignName($requestDTO->campaignName)
            ->setDescription($requestDTO->description ?: null)
            ->setEmailSubject($requestDTO->emailSubject ?: null)
            ->setEmailTemplate($emailTemplate)
            ->setEvent($event)
            ->setEventSession($eventSession)
            ->setEmailCampaignStatus($emailCampaignStatus);

        $this->emailCampaignRepository->save($emailCampaign, true);

        return $emailCampaign;
    }
}
