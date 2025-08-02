<?php

declare(strict_types=1);

namespace App\Controller\CampaignProduct;

use App\Controller\ApiController;
use App\DTO\LoggedInUserDTO;
use App\Module\Stochastic\Feature\CampaignManagement\Voter\CampaignProductVoter;
use App\Service\CampaignProduct\CampaignProductsService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class GetCampaignProductsController extends ApiController
{
    public function __construct(
        private readonly CampaignProductsService $campaignProductsService,
    ) {
    }

    #[Route('/campaign-products', name: 'api_campaign_products_get', methods: ['GET'])]
    public function __invoke(
        LoggedInUserDTO $loggedInUserDTO,
    ): Response {
        //        $this->denyAccessUnlessGranted(CampaignProductVoter::CAMPAIGN_PRODUCT_MANAGE);
        $campaignResponse = $this->campaignProductsService->getProducts();

        return $this->createSuccessResponse($campaignResponse);
    }
}
