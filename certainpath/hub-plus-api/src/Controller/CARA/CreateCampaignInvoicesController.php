<?php

declare(strict_types=1);

namespace App\Controller\CARA;

use App\Controller\ApiController;
use App\DTO\LoggedInUserDTO;
use App\Security\Voter\CaraApiVoter;
use App\Service\CARA\CampaignInvoiceService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class CreateCampaignInvoicesController extends ApiController
{
    public function __construct(
        private readonly CampaignInvoiceService $campaignInvoiceService,
    ) {
    }

    #[Route('/invoice/campaigns', name: 'api_invoice_campaigns', methods: ['POST'])]
    public function __invoke(
        LoggedInUserDTO $loggedInUserDTO,
        Request $request,
    ): Response {
        $this->denyAccessUnlessGranted(CaraApiVoter::CARA_API);

        return $this->createSuccessResponse(
            $this->campaignInvoiceService->createInvoices(
                json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR)
            )
        );
    }
}
