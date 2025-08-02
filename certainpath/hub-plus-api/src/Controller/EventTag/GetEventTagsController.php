<?php

declare(strict_types=1);

namespace App\Controller\EventTag;

use App\Controller\ApiController;
use App\DTO\LoggedInUserDTO;
use App\DTO\Request\EventTag\GetEventTagsRequestDTO;
use App\Service\EventTag\GetEventTagsService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class GetEventTagsController extends ApiController
{
    public function __construct(
        private readonly GetEventTagsService $getEventTagsService,
    ) {
    }

    #[Route(
        '/event-tags',
        name: 'api_event_tags_get',
        methods: ['GET']
    )]
    public function __invoke(
        #[MapQueryString] GetEventTagsRequestDTO $requestDto,
        LoggedInUserDTO $loggedInUserDTO,
        Request $request,
    ): Response {
        $data = $this->getEventTagsService->getTags($requestDto);

        return $this->createSuccessResponse(
            $data,
            $data['totalCount']
        );
    }
}
