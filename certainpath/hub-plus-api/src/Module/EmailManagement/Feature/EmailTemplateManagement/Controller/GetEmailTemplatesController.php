<?php

declare(strict_types=1);

namespace App\Module\EmailManagement\Feature\EmailTemplateManagement\Controller;

use App\Controller\ApiController;
use App\Module\EmailManagement\Feature\EmailTemplateManagement\DTO\Request\GetEmailTemplatesDTO;
use App\Module\EmailManagement\Feature\EmailTemplateManagement\Service\GetEmailTemplateService;
use App\Module\EmailManagement\Feature\EmailTemplateManagement\Voter\EmailTemplateVoter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class GetEmailTemplatesController extends ApiController
{
    public function __construct(
        private readonly GetEmailTemplateService $getEmailTemplateService,
    ) {
    }

    #[Route('/email-templates', name: 'api_email_templates_get', methods: ['GET'])]
    public function __invoke(
        #[MapQueryString] GetEmailTemplatesDTO $queryDto,
    ): Response {
        $this->denyAccessUnlessGranted(EmailTemplateVoter::READ);

        $emailTemplatesData = $this->getEmailTemplateService->getEmailTemplates($queryDto);

        return $this->createSuccessResponse(
            $emailTemplatesData['emailTemplates'],
            $emailTemplatesData['totalCount']
        );
    }
}
