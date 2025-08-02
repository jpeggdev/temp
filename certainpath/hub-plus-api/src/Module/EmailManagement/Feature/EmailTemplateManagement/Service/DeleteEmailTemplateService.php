<?php

declare(strict_types=1);

namespace App\Module\EmailManagement\Feature\EmailTemplateManagement\Service;

use App\Repository\EmailTemplateRepository;

readonly class DeleteEmailTemplateService
{
    public function __construct(
        private EmailTemplateRepository $emailTemplateRepository,
    ) {
    }

    public function deleteEmailTemplate(int $id): void
    {
        $emailTemplate = $this->emailTemplateRepository->findOneByIdOrFail($id);

        $this->emailTemplateRepository->remove($emailTemplate, true);
    }
}
