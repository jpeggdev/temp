<?php

namespace App\Controller\API\Location;

use App\Controller\API\ApiController;
use App\DTO\Query\Location\LocationsQueryDTO;
use App\DTO\Query\PaginationDTO;
use App\Repository\LocationRepository;
use App\Resources\LocationResource;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

class GetLocationsController extends ApiController
{
    public function __construct(
        private readonly LocationResource $locationResource,
        private readonly LocationRepository $locationRepository,
    ) {
    }

    #[Route('/api/locations', name: 'api_locations_get', methods: ['GET'])]
    public function __invoke(
        #[MapQueryString] LocationsQueryDTO $locationsQueryDTO = new LocationsQueryDTO(),
        #[MapQueryString] PaginationDTO $paginationDTO = new PaginationDTO(),
    ): Response {
        $pagination = $this->locationRepository->paginateAll($paginationDTO, $locationsQueryDTO);
        $locationsData = $this->locationResource->transformCollection($pagination['items']);

        return $this->createJsonSuccessResponse($locationsData, $pagination);
    }
}
