<?php

namespace App\Controller\API\CampaignFile;

use App\Controller\API\ApiController;
use App\DTO\Request\Campaign\CreateCampaignFileDTO;
use App\Entity\Campaign;
use App\Exceptions\NotFoundException\MailPackageNotFoundException;
use App\Resources\CampaignFileResource;
use App\Services\CreateCampaignFileService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

class CreateCampaignFileController extends ApiController
{
    public function __construct(
        private readonly CampaignFileResource $campaignFileResource,
        private readonly CreateCampaignFileService $createCampaignFileService,
    ) {
    }

    /**
     * @throws MailPackageNotFoundException
     */
    #[Route('/api/campaign/{id}/file', name: 'api_campaign_file_create', methods: ['POST'])]
    public function __invoke(
        Campaign $campaign,
        #[MapRequestPayload] CreateCampaignFileDTO $fileDto,
        Request $request
    ): Response {
        $campaignFile = $this->createCampaignFileService->createFile($campaign, $fileDto);
        $campaignFileData = $this->campaignFileResource->transformItem($campaignFile);

        return $this->createJsonSuccessResponse($campaignFileData);
    }
}
