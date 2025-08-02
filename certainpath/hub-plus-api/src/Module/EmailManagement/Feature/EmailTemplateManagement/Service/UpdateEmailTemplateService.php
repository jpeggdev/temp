<?php

declare(strict_types=1);

namespace App\Module\EmailManagement\Feature\EmailTemplateManagement\Service;

use App\Entity\EmailTemplate;
use App\Entity\EmailTemplateEmailTemplateCategoryMapping;
use App\Module\EmailManagement\Feature\EmailTemplateManagement\DTO\Request\CreateUpdateEmailTemplateDTO;
use App\Repository\EmailTemplateCategoryRepository;
use App\Repository\EmailTemplateRepository;
use Doctrine\ORM\EntityManagerInterface;

readonly class UpdateEmailTemplateService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private EmailTemplateRepository $emailTemplateRepository,
        private EmailTemplateCategoryRepository $emailTemplateCategoryRepository,
    ) {
    }

    /**
     * @throws \Exception
     */
    public function updateEmailTemplate(
        EmailTemplate $emailTemplate,
        CreateUpdateEmailTemplateDTO $updateEmailTemplateDTO,
    ): void {
        $this->entityManager->beginTransaction();

        try {
            $emailTemplate
                ->setName($updateEmailTemplateDTO->templateName)
                ->setEmailSubject($updateEmailTemplateDTO->emailSubject)
                ->setEmailContent($updateEmailTemplateDTO->emailContent);

            foreach ($emailTemplate->getEmailTemplateEmailTemplateCategoryMappings() as $oldTagMapping) {
                $this->entityManager->remove($oldTagMapping);
            }

            $emailTemplate->getEmailTemplateEmailTemplateCategoryMappings()->clear();
            foreach ($updateEmailTemplateDTO->categoryIds as $categoryId) {
                $emailTemplateCategory = $this->emailTemplateCategoryRepository->find($categoryId);

                if ($emailTemplateCategory) {
                    $emailTemplateEmailTemplateCategoryMapping = (new EmailTemplateEmailTemplateCategoryMapping())
                        ->setEmailTemplate($emailTemplate)
                        ->setEmailTemplateCategory($emailTemplateCategory);
                    $this->entityManager->persist($emailTemplateEmailTemplateCategoryMapping);

                    $emailTemplate->addEmailTemplateEmailTemplateCategoryMapping(
                        $emailTemplateEmailTemplateCategoryMapping
                    );
                }
            }

            $this->emailTemplateRepository->save($emailTemplate, true);
            $this->entityManager->commit();
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            throw $e;
        }
    }
}
