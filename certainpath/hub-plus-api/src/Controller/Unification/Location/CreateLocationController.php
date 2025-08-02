<?php

declare(strict_types=1);

namespace App\Controller\Unification\Location;

use App\Controller\ApiController;
use App\DTO\LoggedInUserDTO;
use App\DTO\Request\Location\CreateUpdateLocationDTO;
use App\Exception\Unification\LocationCreationException;
use App\Service\Unification\Location\CreateLocationService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

#[Route(path: '/api/private')]
class CreateLocationController extends ApiController
{
    public function __construct(
        private readonly CreateLocationService $createLocationService,
    ) {
    }

    /**
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws LocationCreationException
     */
    #[Route('/location/create', name: 'api_location_create', methods: ['POST'])]
    public function __invoke(
        #[MapRequestPayload] CreateUpdateLocationDTO $requestDTO,
        LoggedInUserDTO $loggedInUserDTO,
    ): Response {
        $location = $this->createLocationService->createLocation(
            $requestDTO,
            $loggedInUserDTO->getActiveCompany()->getIntacctId()
        );

        return $this->createSuccessResponse($location);
    }
}
