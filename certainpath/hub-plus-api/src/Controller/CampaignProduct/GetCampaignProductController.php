<?php

namespace App\Controller\CampaignProduct;

use App\Controller\ApiController;
use App\Module\Stochastic\Feature\CampaignManagement\Voter\CampaignProductVoter;
use App\Service\CampaignProductService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class GetCampaignProductController extends ApiController
{
    public function __construct(
        private readonly CampaignProductService $campaignProductService,
    ) {
    }

    #[Route(path: '/campaign-products/{id}', name: 'api_campaign_product_get', methods: ['GET'])]
    public function __invoke(int $id): JsonResponse
    {
        $this->denyAccessUnlessGranted(CampaignProductVoter::CAMPAIGN_PRODUCT_MANAGE);
        $campaignProduct = $this->campaignProductService->getCampaignProductResponseDTOById($id);

        return $this->createSuccessResponse($campaignProduct);
    }
}
