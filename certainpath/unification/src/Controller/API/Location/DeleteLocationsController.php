<?php

namespace App\Controller\API\Location;

use App\Controller\API\ApiController;
use App\Exceptions\NotFoundException\LocationNotFoundException;
use App\Services\Location\DeleteLocationService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DeleteLocationsController extends ApiController
{
    public function __construct(
        private readonly DeleteLocationService $deleteLocationService
    ) {
    }

    /**
     * @throws LocationNotFoundException
     */
    #[Route('/api/location/{id}/delete', name: 'api_location_delete', methods: ['DELETE'])]
    public function __invoke(int $id): Response {
        $this->deleteLocationService->deleteLocation($id);

        return $this->createJsonSuccessResponse([
            'message' => 'Location deleted.',
        ]);
    }
}
