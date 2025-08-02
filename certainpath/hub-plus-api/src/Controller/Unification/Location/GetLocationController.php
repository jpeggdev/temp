<?php

declare(strict_types=1);

namespace App\Controller\Unification\Location;

use App\Controller\ApiController;
use App\Exception\APICommunicationException;
use App\Service\Unification\Location\GetLocationService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;

#[Route(path: '/api/private')]
class GetLocationController extends ApiController
{
    public function __construct(
        private readonly GetLocationService $getLocationService,
    ) {
    }

    /**
     * @throws APICommunicationException
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     */
    #[Route('/location/{id}', name: 'api_location_get', methods: ['GET'])]
    public function __invoke(int $id): Response
    {
        $locationsData = $this->getLocationService->getLocation($id);

        return $this->createSuccessResponse($locationsData);
    }
}
