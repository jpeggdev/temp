<?php

namespace App\Controller\Unification\CampaignFile;

use App\Controller\ApiController;
use App\DTO\LoggedInUserDTO;
use App\DTO\Query\CampaignFile\CampaignFileQueryDTO;
use App\Exception\APICommunicationException;
use App\Service\Unification\CampaignFile\GetCampaignFilesService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;

#[Route(path: '/api/private')]
class GetCampaignFilesController extends ApiController
{
    public function __construct(private readonly GetCampaignFilesService $getCampaignFilesService)
    {
    }

    /**
     * @throws APICommunicationException
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    #[Route('/campaign/{campaignId}/files', name: 'api_campaign_files_get', methods: ['GET'])]
    public function __invoke(
        int $campaignId,
        #[MapQueryString] CampaignFileQueryDTO $queryDto,
        LoggedInUserDTO $loggedInUserDTO,
        Request $request,
    ): Response {
        $filesResponse = $this->getCampaignFilesService->getCampaignFiles(
            $campaignId,
            $queryDto
        );

        return $this->createSuccessResponse(
            $filesResponse['files'],
            $filesResponse['totalCount']
        );
    }
}
