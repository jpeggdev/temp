<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\Shared\Timezone\Controller;

use App\Controller\ApiController;
use App\DTO\Query\PaginationDTO;
use App\Module\EventRegistration\Feature\Shared\Timezone\DTO\Query\GetTimezonesDTO;
use App\Module\EventRegistration\Feature\Shared\Timezone\Service\GetTimezonesService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class GetTimezonesController extends ApiController
{
    public function __construct(
        private readonly GetTimezonesService $getTimezonesService,
    ) {
    }

    #[Route('/timezones', name: 'api_timezones_get', methods: ['GET'])]
    public function __invoke(
        #[MapQueryString] GetTimezonesDTO $queryDTO,
        #[MapQueryString] PaginationDTO $paginationDTO,
    ): Response {
        $timezonesData = $this->getTimezonesService->getTimezones($queryDTO, $paginationDTO);

        return $this->createSuccessResponse(
            $timezonesData['timezones'],
            $timezonesData['totalCount']
        );
    }
}
