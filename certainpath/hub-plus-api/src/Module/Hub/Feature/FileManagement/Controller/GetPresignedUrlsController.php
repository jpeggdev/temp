<?php

declare(strict_types=1);

namespace App\Module\Hub\Feature\FileManagement\Controller;

use App\Controller\ApiController;
use App\Module\Hub\Feature\FileManagement\DTO\Request\GetPresignedUrlsRequestDTO;
use App\Module\Hub\Feature\FileManagement\Service\GetPresignedUrlsService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private/file-management')]
class GetPresignedUrlsController extends ApiController
{
    public function __construct(
        private readonly GetPresignedUrlsService $getPresignedUrlsService,
    ) {
    }

    #[Route('/files/presigned-urls', name: 'api_file_management_get_presigned_urls', methods: ['POST'])]
    public function __invoke(
        #[MapRequestPayload] GetPresignedUrlsRequestDTO $requestDTO,
    ): Response {
        $response = $this->getPresignedUrlsService->getPresignedUrls($requestDTO);
        return $this->createSuccessResponse($response);
    }
}
