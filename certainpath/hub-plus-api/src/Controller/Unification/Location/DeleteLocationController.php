<?php

declare(strict_types=1);

namespace App\Controller\Unification\Location;

use App\Controller\ApiController;
use App\Exception\Unification\LocationDeletionException;
use App\Service\Unification\Location\DeleteLocationService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

#[Route(path: '/api/private')]
class DeleteLocationController extends ApiController
{
    public function __construct(
        private readonly DeleteLocationService $deleteLocationService,
    ) {
    }

    /**
     * @throws ClientExceptionInterface
     * @throws ServerExceptionInterface
     * @throws LocationDeletionException
     * @throws DecodingExceptionInterface
     * @throws TransportExceptionInterface
     * @throws RedirectionExceptionInterface
     */
    #[Route('/location/{id}/delete', name: 'api_location_delete', methods: ['DELETE'])]
    public function __invoke(int $id): Response
    {
        $this->deleteLocationService->deleteLocation($id);

        return $this->createSuccessResponse([
            'message' => sprintf('Location %d has been deleted.', $id),
        ]);
    }
}
