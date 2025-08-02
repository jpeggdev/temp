<?php

declare(strict_types=1);

namespace App\Module\EmailManagement\Feature\EmailCampaignManagement\Service;

use App\Client\MailchimpTransactionalClient;
use App\Module\EmailManagement\Feature\EmailCampaignManagement\DTO\Request\SendCampaignEmailDTO;
use App\Module\EmailManagement\Feature\EmailTemplateManagement\Service\EmailTemplateVariableResolverService;
use App\Repository\EmailTemplateRepository;
use App\Repository\EventSession\EventSessionRepository;

readonly class SendCampaignEmailService
{
    public const string FEATURE_EMAIL_CAMPAIGN = 'email_campaign';

    public function __construct(
        private string $appEmailAddress,
        private EmailTemplateRepository $emailTemplateRepository,
        private EventSessionRepository $eventSessionRepository,
        private MailchimpTransactionalClient $mailchimpTransactionalClient,
        private EmailTemplateVariableResolverService $emailTemplateVariableResolverService,
    ) {
    }

    public function sendEmail(SendCampaignEmailDTO $sendCampaignEmailDTO): bool
    {
        $emailTemplate = $this->emailTemplateRepository->findOneByIdOrFail($sendCampaignEmailDTO->emailTemplateId);
        $eventSession = $this->eventSessionRepository->findOneByIdOrFail($sendCampaignEmailDTO->sessionId);
        $emailSubject = $sendCampaignEmailDTO->emailSubject ?: $emailTemplate->getEmailSubject();
        $emailRecipients = $sendCampaignEmailDTO->emailRecipients;
        $htmlContent = $this->emailTemplateVariableResolverService->resolveEmailTemplateContent(
            $emailTemplate,
            $eventSession,
        );
        $metadata = [
            'feature' => self::FEATURE_EMAIL_CAMPAIGN,
        ];

        $this->mailchimpTransactionalClient->sendEmail(
            $this->appEmailAddress,
            $emailRecipients,
            $emailSubject,
            $htmlContent,
            '',
            $metadata
        );

        return true;
    }
}
