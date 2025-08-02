<?php

namespace App\Controller\CampaignProduct;

use App\Controller\ApiController;
use App\DTO\CampaignProduct\CampaignProductRequestDTO;
use App\Module\Stochastic\Feature\CampaignManagement\Voter\CampaignProductVoter;
use App\Service\CampaignProductService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class UpdateCampaignProductController extends ApiController
{
    public function __construct(
        private readonly CampaignProductService $campaignProductService,
    ) {
    }

    #[Route(path: '/campaign-products/{id}', name: 'api_campaign_product_update', methods: ['PUT'])]
    public function __invoke(int $id, #[MapRequestPayload] CampaignProductRequestDTO $requestDTO): JsonResponse
    {
        $this->denyAccessUnlessGranted(CampaignProductVoter::CAMPAIGN_PRODUCT_MANAGE);
        $campaignProduct = $this->campaignProductService->updateCampaignProductFromRequestDTO($id, $requestDTO);

        return $this->createSuccessResponse($campaignProduct);
    }
}
