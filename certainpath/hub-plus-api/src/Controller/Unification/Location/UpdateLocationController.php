<?php

declare(strict_types=1);

namespace App\Controller\Unification\Location;

use App\Controller\ApiController;
use App\DTO\LoggedInUserDTO;
use App\DTO\Request\Location\CreateUpdateLocationDTO;
use App\Exception\APICommunicationException;
use App\Service\Unification\Location\UpdateLocationService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;

#[Route(path: '/api/private')]
class UpdateLocationController extends ApiController
{
    public function __construct(
        private readonly UpdateLocationService $updateLocationService,
    ) {
    }

    /**
     * @throws APICommunicationException
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     */
    #[Route('/location/{id}', name: 'api_location_put', methods: ['PUT'])]
    public function __invoke(
        int $id,
        LoggedInUserDTO $loggedInUserDTO,
        #[MapRequestPayload] CreateUpdateLocationDTO $updateCreateLocationDTO,
    ): Response {
        $locationData = $this->updateLocationService->updateLocation(
            $id,
            $loggedInUserDTO->getActiveCompany()->getIntacctId(),
            $updateCreateLocationDTO
        );

        return $this->createSuccessResponse($locationData);
    }
}
