<?php

namespace App\Controller\API\Location;

use App\Controller\API\ApiController;
use App\DTO\Request\Location\CreateUpdateLocationDTO;
use App\Exceptions\NotFoundException\CompanyNotFoundException;
use App\Resources\LocationResource;
use App\Services\Location\CreateLocationService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

class CreateLocationController extends ApiController
{
    public function __construct(
        private readonly CreateLocationService $createLocationService,
        private readonly LocationResource $locationResource,
    ) {
    }

    /**
     * @throws CompanyNotFoundException
     */
    #[Route('/api/location/create', name: 'api_location_create', methods: ['POST'])]
    public function __invoke(
        #[MapRequestPayload] CreateUpdateLocationDTO $createLocationDTO
    ): Response {
        $location = $this->createLocationService->createLocation($createLocationDTO);
        $locationData = $this->locationResource->transformItem($location);


        return $this->createJsonSuccessResponse($locationData);
    }
}
