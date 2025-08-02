<?php

namespace App\Controller\API\Campaign;

use App\Controller\API\ApiController;
use App\Exceptions\DomainException\Campaign\CampaignResumeFailedException;
use App\Exceptions\NotFoundException\CampaignEventNotFoundException;
use App\Exceptions\NotFoundException\CampaignNotFoundException;
use App\Repository\CampaignRepository;
use App\Services\Campaign\ResumeCampaignService;
use App\Resources\CampaignResource;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ResumeCampaignController extends ApiController
{
    public function __construct(
        private readonly CampaignResource $campaignResource,
        private readonly CampaignRepository $campaignRepository,
        private readonly ResumeCampaignService $resumeCampaignService,
    ) {
    }

    /**
     * @throws CampaignNotFoundException
     * @throws CampaignResumeFailedException
     * @throws CampaignEventNotFoundException
     */
    #[Route('/api/campaign/resume-async/{id}', name: 'api_campaign_resume', methods: ['PATCH'])]
    public function __invoke(int $id): Response
    {
        $campaign = $this->campaignRepository->findOneByIdOrFail($id);

        $this->resumeCampaignService->resume($campaign);
        $campaignData = $this->campaignResource->transformItem($campaign);

        return $this->createJsonSuccessResponse($campaignData);
    }
}
