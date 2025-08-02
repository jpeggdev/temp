<?php

namespace App\Controller\API\Location;

use App\Controller\API\ApiController;
use App\Exceptions\NotFoundException\LocationNotFoundException;
use App\Repository\LocationRepository;
use App\Resources\LocationResource;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class GetLocationController extends ApiController
{
    public function __construct(
        private readonly LocationRepository $locationRepository,
        private readonly LocationResource $locationResource,
    ) {
    }

    /**
     * @throws LocationNotFoundException
     */
    #[Route('/api/location/{id}', name: 'api_location_get', methods: ['GET'])]
    public function __invoke(int $id): Response
    {
        $location = $this->locationRepository->findOneByIdOrFail($id);
        $locationData = $this->locationResource->transformItem($location);


        return $this->createJsonSuccessResponse($locationData);
    }
}
