<?php

namespace App\Controller\Unification\CampaignFile;

use App\Controller\ApiController;
use App\DTO\LoggedInUserDTO;
use App\DTO\Request\CampaignFile\DownloadCampaignFileDTO;
use App\Service\Unification\CampaignFile\DownloadCampaignFileService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class DownloadCampaignFilesController extends ApiController
{
    public function __construct(private readonly DownloadCampaignFileService $downloadCampaignFileService)
    {
    }

    #[Route('/campaign/file/download', name: 'api_campaign_file_download', methods: ['GET'])]
    public function __invoke(
        #[MapQueryString] DownloadCampaignFileDTO $queryDto,
        LoggedInUserDTO $loggedInUserDTO,
        Request $request,
    ): Response {
        return $this->downloadCampaignFileService->downloadFile(
            $queryDto->bucketName,
            $queryDto->objectKey
        );
    }
}
