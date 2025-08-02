<?php

declare(strict_types=1);

namespace App\Module\EmailManagement\Feature\EmailCampaignManagement\Service;

use App\Entity\EmailCampaignStatus;
use App\Module\EmailManagement\Feature\EmailCampaignManagement\DTO\Response\GetEmailCampaignResponseDTO;
use App\Module\EmailManagement\Feature\EmailCampaignManagement\Exception\FailedToDeleteEmailCampaignException;
use App\Repository\EmailCampaignRepository;
use App\Repository\EmailCampaignStatusRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

readonly class DeleteEmailCampaignService
{
    public function __construct(
        private LoggerInterface $logger,
        private EntityManagerInterface $entityManager,
        private EmailCampaignRepository $emailCampaignRepository,
        private EmailCampaignStatusRepository $emailCampaignStatusRepository,
    ) {
    }

    /**
     * @throws FailedToDeleteEmailCampaignException
     */
    public function deleteEmailCampaign(int $id): GetEmailCampaignResponseDTO
    {
        $emailCampaignStatusArchived = $this->emailCampaignStatusRepository->findOneByNameOrFail(
            EmailCampaignStatus::STATUS_ARCHIVED
        );
        $emailCampaignToDelete = $this->emailCampaignRepository->findOneByIdOrFail($id);

        $this->entityManager->beginTransaction();

        try {
            $emailCampaignToDelete->setIsActive(false);
            $emailCampaignToDelete->setDeletedAt(new \DateTimeImmutable('now'));
            $emailCampaignToDelete->setEmailCampaignStatus($emailCampaignStatusArchived);

            $this->entityManager->persist($emailCampaignToDelete);
            $this->entityManager->flush();
            $this->entityManager->commit();
        } catch (\Exception $e) {
            $this->entityManager->rollback();

            $message = sprintf(
                'Failed to delete email campaign (ID: %d): %s',
                $emailCampaignToDelete->getId(),
                $e->getMessage()
            );
            $this->logger->error($message);

            throw new FailedToDeleteEmailCampaignException();
        }

        return GetEmailCampaignResponseDTO::fromEntity($emailCampaignToDelete);
    }
}
