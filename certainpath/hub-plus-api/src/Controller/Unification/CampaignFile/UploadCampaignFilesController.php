<?php

declare(strict_types=1);

namespace App\Controller\Unification\CampaignFile;

use App\Controller\ApiController;
use App\Module\Stochastic\Feature\Uploads\Service\UploadCampaignFilesService;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class UploadCampaignFilesController extends ApiController
{
    private UploadCampaignFilesService $uploadCampaignFilesService;

    public function __construct(UploadCampaignFilesService $uploadCampaignFilesService)
    {
        $this->uploadCampaignFilesService = $uploadCampaignFilesService;
    }

    #[Route('/campaign/{campaignId}/upload-file', name: 'api_campaign_upload_file', methods: ['POST'])]
    public function __invoke(int $campaignId, Request $request): Response
    {
        /** @var UploadedFile|null $file */
        $file = $request->files->get('file');

        if (!$file) {
            return $this->json(['error' => 'No file was uploaded.'], Response::HTTP_BAD_REQUEST);
        }

        $fileUrl = $this->uploadCampaignFilesService->uploadAndCreateCampaignFile($campaignId, $file);

        return $this->json(['fileUrl' => $fileUrl], Response::HTTP_OK);
    }
}
