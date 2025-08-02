<?php

namespace App\Controller\API\Location;

use App\Controller\API\ApiController;
use App\DTO\Request\Location\CreateUpdateLocationDTO;
use App\Exceptions\NotFoundException\LocationNotFoundException;
use App\Resources\LocationResource;
use App\Services\Location\UpdateLocationService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

class UpdateLocationController extends ApiController
{
    public function __construct(
        private readonly UpdateLocationService $updateLocationService,
        private readonly LocationResource $locationResource,
    ) {
    }

    /**
     * @throws LocationNotFoundException
     */
    #[Route('/api/location/{id}', name: 'api_location_put', methods: ['PUT'])]
    public function __invoke(
        int $id,
        #[MapRequestPayload] CreateUpdateLocationDTO $updateLocationDTO
    ): Response {
        $location = $this->updateLocationService->updateLocation($id, $updateLocationDTO);
        $locationData = $this->locationResource->transformItem($location);

        return $this->createJsonSuccessResponse($locationData);
    }
}
