<?php

namespace App\Controller\API\Campaign;

use App\Controller\API\ApiController;
use App\DTO\Request\Campaign\CreateCampaignDTO;
use App\Exceptions\DomainException\Campaign\CampaignAlreadyCreatedException;
use App\Exceptions\DomainException\Campaign\CampaignAlreadyProcessingException;
use App\Exceptions\DomainException\Campaign\CampaignCreationFailedException;
use App\Exceptions\NotFoundException\CampaignStatusNotFoundException;
use App\Exceptions\NotFoundException\CompanyNotFoundException;
use App\Resources\CampaignResource;
use App\Services\Campaign\CreateCampaignService;
use Doctrine\ORM\Exception\ORMException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

class CreateCampaignAsyncController extends ApiController
{
    public function __construct(
        private readonly CreateCampaignService $campaignService,
        private readonly CampaignResource $campaignResource,
    ) {
    }

    /**
     * @throws ORMException
     * @throws CompanyNotFoundException
     * @throws CampaignAlreadyCreatedException
     * @throws CampaignAlreadyProcessingException
     * @throws CampaignCreationFailedException
     * @throws CampaignStatusNotFoundException
     */
    #[Route('/api/campaign/create-async', name: 'api_campaign_create_async', methods: ['POST'])]
    public function __invoke(
        #[MapRequestPayload] CreateCampaignDTO $createCampaignRequestDTO
    ): Response {
        $campaign = $this->campaignService->createCampaignAsync($createCampaignRequestDTO);
        $campaignData = $this->campaignResource->transformItem($campaign);

        return $this->createJsonSuccessResponse($campaignData);
    }
}
