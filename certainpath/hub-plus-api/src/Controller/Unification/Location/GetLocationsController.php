<?php

declare(strict_types=1);

namespace App\Controller\Unification\Location;

use App\Controller\ApiController;
use App\DTO\LoggedInUserDTO;
use App\DTO\Query\Location\LocationDTO;
use App\Exception\APICommunicationException;
use App\Service\Unification\Location\GetLocationsService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;

#[Route(path: '/api/private')]
class GetLocationsController extends ApiController
{
    public function __construct(
        private readonly GetLocationsService $getLocationsService,
    ) {
    }

    /**
     * @throws APICommunicationException
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     */
    #[Route('/locations', name: 'api_locations_get', methods: ['GET'])]
    public function __invoke(
        #[MapQueryString] LocationDTO $queryDTO,
        LoggedInUserDTO $loggedInUserDTO,
    ): Response {
        $intacctId = $loggedInUserDTO->getActiveCompany()->getIntacctId();
        $locationsData = $this->getLocationsService->getLocations($queryDTO, $intacctId);

        return $this->createSuccessResponse(
            $locationsData['locations'],
            $locationsData['totalCount'],
        );
    }
}
