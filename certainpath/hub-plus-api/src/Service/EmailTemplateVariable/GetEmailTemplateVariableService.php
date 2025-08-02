<?php

declare(strict_types=1);

namespace App\Service\EmailTemplateVariable;

use App\DTO\Request\EmailTemplateVariable\GetEmailTemplateVariablesDTO;
use App\DTO\Response\EmailTemplateVariable\GetEmailTemplateVariableResponseDTO;
use App\Entity\EmailTemplateVariable;
use App\Repository\EmailTemplateVariableRepository;

readonly class GetEmailTemplateVariableService
{
    public function __construct(
        private EmailTemplateVariableRepository $emailTemplateVariableRepository,
    ) {
    }

    public function getEmailTemplateVariables(GetEmailTemplateVariablesDTO $queryDto): array
    {
        $emailTemplateVariables = $this->emailTemplateVariableRepository->findAllByQuery($queryDto);
        $totalCount = $this->emailTemplateVariableRepository->getTotalCount($queryDto);

        $emailTemplateVariablesDTOs = array_map(
            static fn (EmailTemplateVariable $emailTemplateVariable) => GetEmailTemplateVariableResponseDTO::fromEntity(
                $emailTemplateVariable
            ),
            $emailTemplateVariables
        );

        return [
            'emailTemplateVariables' => $emailTemplateVariablesDTOs,
            'totalCount' => $totalCount,
        ];
    }
}
