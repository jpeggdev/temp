<?php

namespace App\Controller\API\Campaign;

use App\Controller\API\ApiController;
use App\DTO\Query\PaginationDTO;
use App\Exceptions\NotFoundException\CampaignNotFoundException;
use App\Repository\CampaignRepository;
use App\Resources\CampaignResource;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

class GetCampaignController extends ApiController
{
    public function __construct(
        private readonly CampaignResource $campaignResource,
        private readonly CampaignRepository $campaignRepository,
    ) {
    }

    /**
     * @throws CampaignNotFoundException
     */
    #[Route('/api/campaign/{id}', name: 'api_campaign_get', methods: ['GET'])]
    public function __invoke(
        int $id,
        #[MapQueryString] PaginationDTO $paginationDTO = new PaginationDTO(),
    ): Response {
        $campaign = $this->campaignRepository->findOneById($id);
        if (!$campaign) {
            throw new CampaignNotFoundException();
        }

        $includes = $paginationDTO->includes;
        $campaignData = $this->campaignResource->transformItem($campaign, $includes);

        return $this->createJsonSuccessResponse($campaignData);
    }
}
