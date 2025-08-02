<?php

namespace App\Module\EmailManagement\Feature\EmailCampaignManagement\Service;

use App\Entity\EmailCampaign;
use App\Entity\EmailCampaignEventEnrollment;
use App\Entity\EmailCampaignStatus;
use App\Entity\EventEnrollment;
use App\Exception\UnsupportedSendOptionException;
use App\Module\EmailManagement\Feature\EmailCampaignManagement\DTO\Request\CreateUpdateEmailCampaignDTO;
use App\Module\EmailManagement\Feature\EmailCampaignManagement\DTO\Request\SendCampaignEmailDTO;
use App\Module\EmailManagement\Feature\EmailCampaignManagement\Trait\ResolveEmailCampaignStatusTrait;
use App\Repository\EmailCampaignRepository;
use App\Repository\EmailCampaignStatusRepository;
use App\Repository\EmailTemplateRepository;
use App\Repository\EventEnrollmentRepository;
use App\Repository\EventRepository\EventRepository;
use App\Repository\EventSession\EventSessionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;

readonly class CreateEmailCampaignService
{
    use ResolveEmailCampaignStatusTrait;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private EventRepository $eventRepository,
        private EventSessionRepository $eventSessionRepository,
        private EventEnrollmentRepository $eventEnrollmentRepository,
        private EmailTemplateRepository $emailTemplateRepository,
        private EmailCampaignRepository $emailCampaignRepository,
        private EmailCampaignStatusRepository $emailCampaignStatusRepository,
        private SendCampaignEmailService $sendCampaignEmailsService,
    ) {
    }

    /**
     * @throws UnsupportedSendOptionException
     */
    public function createCampaign(CreateUpdateEmailCampaignDTO $requestDTO): EmailCampaign
    {
        $event = $this->eventRepository->findOneByIdOrFail(
            $requestDTO->eventId
        );
        $eventSession = $this->eventSessionRepository->findOneByIdOrFail(
            $requestDTO->sessionId
        );
        $emailTemplate = $this->emailTemplateRepository->findOneByIdOrFail(
            $requestDTO->emailTemplateId
        );
        $eventEnrollments = $this->eventEnrollmentRepository->findAllByEventSessionId(
            $eventSession->getId(),
        );
        $emailCampaignStatus = $this->resolveEmailCampaignStatus(
            $requestDTO->sendOption
        );

        $emailCampaign = (new EmailCampaign())
            ->setCampaignName($requestDTO->campaignName)
            ->setDescription($requestDTO->description ?: null)
            ->setEmailSubject($requestDTO->emailSubject ?: null)
            ->setEmailTemplate($emailTemplate)
            ->setEvent($event)
            ->setEventSession($eventSession)
            ->setEmailCampaignStatus($emailCampaignStatus);

        if (EmailCampaignStatus::STATUS_SENT === $emailCampaignStatus->getName()) {
            foreach ($eventEnrollments as $eventEnrollment) {
                $emailCampaignEventEnrollment = (new EmailCampaignEventEnrollment())
                    ->setEmailCampaign($emailCampaign)
                    ->setEventEnrollment($eventEnrollment);

                $emailCampaign->addEmailCampaignEventEnrollment(
                    $emailCampaignEventEnrollment
                );

                $this->entityManager->persist($emailCampaignEventEnrollment);
            }

            $sendCampaignEmailDTO = $this->prepareSendCampaignEmailDTO($requestDTO, $eventEnrollments);
            $this->sendCampaignEmailsService->sendEmail($sendCampaignEmailDTO);
            $emailCampaign->setDateSent(new \DateTimeImmutable());
        }

        $this->emailCampaignRepository->save($emailCampaign, true);

        return $emailCampaign;
    }

    /**
     * @param ArrayCollection<int, EventEnrollment> $eventEnrollments
     */
    private function prepareSendCampaignEmailDTO(
        CreateUpdateEmailCampaignDTO $dto,
        ArrayCollection $eventEnrollments,
    ): SendCampaignEmailDTO {
        $emailRecipients = [];

        /* @var EventEnrollment $eventEnrollment */
        foreach ($eventEnrollments as $enrollment) {
            $email = $enrollment->getEmail();

            if (!empty($email)) {
                $emailRecipients[] = $email;
            }
        }

        return new SendCampaignEmailDTO(
            $dto->emailTemplateId,
            $dto->eventId,
            $dto->sessionId,
            $dto->emailSubject,
            $emailRecipients,
        );
    }
}
