<?php

declare(strict_types=1);

namespace App\Controller\EmailTemplateVariable;

use App\Controller\ApiController;
use App\DTO\Request\EmailTemplateVariable\GetEmailTemplateVariablesDTO;
use App\Service\EmailTemplateVariable\GetEmailTemplateVariableService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class GetEmailTemplateVariablesController extends ApiController
{
    public function __construct(
        private readonly GetEmailTemplateVariableService $getEmailTemplateVariableService,
    ) {
    }

    #[Route('/email-template-variables', name: 'api_email_template_variables_get', methods: ['GET'])]
    public function __invoke(
        #[MapQueryString] GetEmailTemplateVariablesDTO $queryDto = new GetEmailTemplateVariablesDTO(),
    ): Response {
        $emailTemplateVariablesData = $this->getEmailTemplateVariableService->getEmailTemplateVariables($queryDto);

        return $this->createSuccessResponse(
            $emailTemplateVariablesData['emailTemplateVariables'],
            $emailTemplateVariablesData['totalCount'],
        );
    }
}
