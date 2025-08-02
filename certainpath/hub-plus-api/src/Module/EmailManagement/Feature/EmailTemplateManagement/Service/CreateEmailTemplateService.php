<?php

declare(strict_types=1);

namespace App\Module\EmailManagement\Feature\EmailTemplateManagement\Service;

use App\Entity\EmailTemplate;
use App\Entity\EmailTemplateEmailTemplateCategoryMapping;
use App\Module\EmailManagement\Feature\EmailTemplateManagement\DTO\Request\CreateUpdateEmailTemplateDTO;
use App\Module\EmailManagement\Feature\EmailTemplateManagement\DTO\Response\CreateEmailTemplateResponseDTO;
use App\Repository\EmailTemplateCategoryRepository;
use App\Repository\EmailTemplateRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;

readonly class CreateEmailTemplateService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private EmailTemplateRepository $emailTemplateRepository,
        private EmailTemplateCategoryRepository $emailTemplateCategoryRepository,
    ) {
    }

    public function createEmailTemplate(
        CreateUpdateEmailTemplateDTO $createEmailTemplateDTO,
    ): CreateEmailTemplateResponseDTO {
        $emailTemplateCategories = new ArrayCollection();
        foreach ($createEmailTemplateDTO->categoryIds as $categoryId) {
            $emailTemplateCategory = $this->emailTemplateCategoryRepository->findOneByIdOrFail($categoryId);
            $emailTemplateCategories->add($emailTemplateCategory);
        }

        $emailTemplate = (new EmailTemplate())
            ->setName($createEmailTemplateDTO->templateName)
            ->setEmailSubject($createEmailTemplateDTO->emailSubject)
            ->setEmailContent($createEmailTemplateDTO->emailContent);

        foreach ($emailTemplateCategories as $emailTemplateCategory) {
            $emailTemplateEmailTemplateCategoryMapping = (new EmailTemplateEmailTemplateCategoryMapping())
                ->setEmailTemplate($emailTemplate)
                ->setEmailTemplateCategory($emailTemplateCategory);

            $emailTemplate->addEmailTemplateEmailTemplateCategoryMapping(
                $emailTemplateEmailTemplateCategoryMapping
            );

            $this->entityManager->persist($emailTemplateEmailTemplateCategoryMapping);
        }

        $this->emailTemplateRepository->save($emailTemplate, true);

        return CreateEmailTemplateResponseDTO::fromEntity($emailTemplate);
    }
}
