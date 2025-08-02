<?php

namespace App\Module\EmailManagement\Feature\EmailCampaignManagement\Service;

use App\Entity\EmailCampaignActivityLog;
use App\Repository\EmailCampaignActivityLogRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

readonly class ProcessEmailCampaignWebhookService
{
    public function __construct(
        private EntityManagerInterface $em,
        private EmailCampaignActivityLogRepository $emailCampaignActivityLogRepository,
    ) {
    }

    /**
     * @throws \JsonException
     */
    public function processWebhook(Request $request): bool
    {
        $mandrillEvents = $request->request->get('mandrill_events');
        $mandrillEventsData = json_decode($mandrillEvents, true, 512, JSON_THROW_ON_ERROR);

        if (!is_array($mandrillEventsData)) {
            return false;
        }

        foreach ($mandrillEventsData as $event) {
            $this->processEvent($event);
        }

        return true;
    }

    private function processEvent(array $event): void
    {
        $eventType = $event['event'] ?? '';
        $email = $event['msg']['email'] ?? null;
        $subject = $event['msg']['subject'] ?? null;
        $messageId = $event['_id'] ?? null;
        $metadata = $event['msg']['metadata'] ?? [];
        $timestamp = $event['ts'] ?? null;
        $eventTimestamp = $timestamp
            ? (new \DateTimeImmutable())->setTimestamp($timestamp)
            : new \DateTimeImmutable();

        if (!$this->shouldProcessEvent($eventType, $messageId, $email, $metadata)) {
            return;
        }

        if (EmailCampaignActivityLog::EVENT_TYPE_SEND === $eventType) {
            $this->createInitialLog($messageId, $email, $subject, $eventTimestamp);

            return;
        }

        $this->updateLogStatus($messageId, $email, $subject, $eventType);
    }

    private function createInitialLog(
        string $messageId,
        string $email,
        ?string $subject,
        \DateTimeImmutable $timestamp,
    ): EmailCampaignActivityLog {
        $existingLog = $this->emailCampaignActivityLogRepository->findOneByMessageId($messageId);

        if ($existingLog) {
            return $existingLog;
        }

        $log = (new EmailCampaignActivityLog())
            ->setMessageId($messageId)
            ->setEmail($email)
            ->setSubject($subject)
            ->setEventSentAt($timestamp)
            ->setIsSent(true);

        $this->em->persist($log);
        $this->em->flush();

        return $log;
    }

    private function updateLogStatus(
        string $messageId,
        string $email,
        ?string $subject,
        string $eventType,
    ): void {
        $log = $this->emailCampaignActivityLogRepository->findOneByMessageId($messageId)
            ?? $this->createInitialLog($messageId, $email, $subject, new \DateTimeImmutable());

        match ($eventType) {
            EmailCampaignActivityLog::EVENT_TYPE_DELIVERED => $log->setIsDelivered(true),
            EmailCampaignActivityLog::EVENT_TYPE_OPEN => $log->setIsOpened(true),
            EmailCampaignActivityLog::EVENT_TYPE_CLICK => $log->setIsClicked(true),
            EmailCampaignActivityLog::EVENT_TYPE_SPAM => $log->setIsMarkedAsSpam(true),
            EmailCampaignActivityLog::EVENT_TYPE_HARD_BOUNCE => $log->setIsBounced(true),
            default => null,
        };

        $this->em->persist($log);
        $this->em->flush();
    }

    private function shouldProcessEvent(
        ?string $eventType,
        ?string $messageId,
        ?string $email,
        array $metadata,
    ): bool {
        return
            $email
            && $messageId
            && in_array($eventType, $this->getTrackableEvents(), true)
            && ($metadata['feature'] ?? null) === SendCampaignEmailService::FEATURE_EMAIL_CAMPAIGN
        ;
    }

    private function getTrackableEvents(): array
    {
        return [
            EmailCampaignActivityLog::EVENT_TYPE_SEND,
            EmailCampaignActivityLog::EVENT_TYPE_DELIVERED,
            EmailCampaignActivityLog::EVENT_TYPE_OPEN,
            EmailCampaignActivityLog::EVENT_TYPE_CLICK,
            EmailCampaignActivityLog::EVENT_TYPE_SPAM,
            EmailCampaignActivityLog::EVENT_TYPE_HARD_BOUNCE,
        ];
    }
}
