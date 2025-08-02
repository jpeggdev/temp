<?php

declare(strict_types=1);

namespace App\Module\EmailManagement\Feature\EmailTemplateManagement\Service;

use App\Entity\EmailTemplate;
use App\Module\EmailManagement\Feature\EmailTemplateManagement\DTO\Request\GetEmailTemplatesDTO;
use App\Module\EmailManagement\Feature\EmailTemplateManagement\DTO\Response\GetEmailTemplateResponseDTO;
use App\Repository\EmailTemplateRepository;

readonly class GetEmailTemplateService
{
    public function __construct(
        private EmailTemplateRepository $emailTemplateRepository,
    ) {
    }

    public function getEmailTemplate(int $id): GetEmailTemplateResponseDTO
    {
        $emailTemplate = $this->emailTemplateRepository->findOneByIdOrFail($id);

        return GetEmailTemplateResponseDTO::fromEntity($emailTemplate);
    }

    public function getEmailTemplates(GetEmailTemplatesDTO $queryDto): array
    {
        $emailTemplates = $this->emailTemplateRepository->findAllByQuery($queryDto);
        $totalCount = $this->emailTemplateRepository->getTotalCount($queryDto);

        $emailTemplateDTOs = array_map(
            static fn (EmailTemplate $emailTemplate) => GetEmailTemplateResponseDTO::fromEntity($emailTemplate),
            $emailTemplates
        );

        return [
            'emailTemplates' => $emailTemplateDTOs,
            'totalCount' => $totalCount,
        ];
    }
}
