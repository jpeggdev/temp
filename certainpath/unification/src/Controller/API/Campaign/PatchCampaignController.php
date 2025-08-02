<?php

namespace App\Controller\API\Campaign;

use App\Controller\API\ApiController;
use App\DTO\Query\PaginationDTO;
use App\DTO\Request\Campaign\PatchCampaignDTO;
use App\Exceptions\NotFoundException\BatchStatusNotFoundException;
use App\Exceptions\NotFoundException\CampaignNotFoundException;
use App\Repository\CampaignRepository;
use App\Resources\CampaignResource;
use App\Services\Campaign\PatchCampaignService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

class PatchCampaignController extends ApiController
{
    public function __construct(
        private readonly CampaignRepository $campaignRepository,
        private readonly CampaignResource $campaignResource,
        private readonly PatchCampaignService $patchCampaignService
    ) {
    }

    /**
     * @throws CampaignNotFoundException
     * @throws BatchStatusNotFoundException
     */
    #[Route('/api/campaign/{id}', name: 'api_campaign_patch', methods: ['PATCH'])]
    public function __invoke(
        int $id,
        #[MapRequestPayload] PatchCampaignDTO $patchCampaignDTO = new PatchCampaignDTO(),
        #[MapQueryString] PaginationDTO $paginationDTO = new PaginationDTO(),
    ): Response {
        $campaign = $this->campaignRepository->findOneByIdOrFail($id);

        $this->patchCampaignService->patchCampaign($campaign, $patchCampaignDTO);
        $campaignData = $this->campaignResource->transformItem($campaign);

        return $this->createJsonSuccessResponse($campaignData);
    }
}
