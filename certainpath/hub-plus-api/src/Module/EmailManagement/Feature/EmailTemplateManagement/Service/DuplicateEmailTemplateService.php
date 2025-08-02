<?php

declare(strict_types=1);

namespace App\Module\EmailManagement\Feature\EmailTemplateManagement\Service;

use App\Entity\EmailTemplate;
use App\Entity\EmailTemplateEmailTemplateCategoryMapping;
use App\Repository\EmailTemplateRepository;
use Doctrine\ORM\EntityManagerInterface;

readonly class DuplicateEmailTemplateService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private EmailTemplateRepository $emailTemplateRepository,
    ) {
    }

    public function duplicateEmailTemplate(int $id): void
    {
        $emailTemplate = $this->emailTemplateRepository->findOneByIdOrFail($id);

        $duplicatedTemplate = (new EmailTemplate())
            ->setName($emailTemplate->getName().' (Copy)')
            ->setEmailSubject($emailTemplate->getEmailSubject())
            ->setEmailContent($emailTemplate->getEmailContent())
            ->setActive($emailTemplate->isActive());

        foreach ($emailTemplate->getEmailTemplateEmailTemplateCategoryMappings() as $existingMapping) {
            $mappingDuplicated = (new EmailTemplateEmailTemplateCategoryMapping())
                ->setEmailTemplate($duplicatedTemplate)
                ->setEmailTemplateCategory($existingMapping->getEmailTemplateCategory());

            $this->entityManager->persist($mappingDuplicated);
            $duplicatedTemplate->addEmailTemplateEmailTemplateCategoryMapping($mappingDuplicated);
        }

        $this->emailTemplateRepository->save($duplicatedTemplate, true);
    }
}
