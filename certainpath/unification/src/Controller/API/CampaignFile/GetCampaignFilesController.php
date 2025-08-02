<?php

namespace App\Controller\API\CampaignFile;

use App\Controller\API\ApiController;
use App\DTO\Query\Campaign\CampaignFileQueryDTO;
use App\Entity\Campaign;
use App\Services\GetCampaignFilesService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

class GetCampaignFilesController extends ApiController
{
    public function __construct(private readonly GetCampaignFilesService $getCampaignFilesService)
    {
    }

    #[Route('/api/campaign/{id}/files', name: 'api_campaign_files_get', methods: ['GET'])]
    public function __invoke(
        Campaign $campaign,
        #[MapQueryString] CampaignFileQueryDTO $queryDto,
        Request $request
    ): Response {
        $filesData = $this->getCampaignFilesService->getFiles($campaign, $queryDto);

        return $this->createJsonSuccessResponse(
            $filesData['files'],
            $filesData
        );
    }
}
