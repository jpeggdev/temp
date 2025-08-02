<?php

declare(strict_types=1);

namespace App\Controller\ResourceTag;

use App\Controller\ApiController;
use App\DTO\LoggedInUserDTO;
use App\DTO\Request\ResourceTag\GetResourceTagsRequestDTO;
use App\Service\ResourceTag\GetResourceTagsService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class GetResourceTagsController extends ApiController
{
    public function __construct(
        private readonly GetResourceTagsService $getResourceTagsService,
    ) {
    }

    #[Route('/resource-tags', name: 'api_resource_tags_get', methods: ['GET'])]
    public function __invoke(
        #[MapQueryString] GetResourceTagsRequestDTO $requestDto,
        LoggedInUserDTO $loggedInUserDTO,
        Request $request,
    ): Response {
        $data = $this->getResourceTagsService->getTags($requestDto);

        return $this->createSuccessResponse(
            $data,
            $data['totalCount']
        );
    }
}
