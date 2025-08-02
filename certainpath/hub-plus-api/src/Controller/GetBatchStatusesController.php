<?php

declare(strict_types=1);

namespace App\Controller;

use App\Exception\APICommunicationException;
use App\Exception\BatchStatusesNotFoundException;
use App\Service\Unification\GetBatchStatusesService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;

#[Route(path: '/api/private')]
class GetBatchStatusesController extends ApiController
{
    public function __construct(
        private readonly GetBatchStatusesService $getBatchStatusesService,
    ) {
    }

    /**
     * @throws ServerExceptionInterface
     * @throws ClientExceptionInterface
     * @throws APICommunicationException
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws BatchStatusesNotFoundException
     */
    #[Route('/batch-statuses', name: 'api_batch_statuses_get', methods: ['GET'])]
    public function __invoke(): Response
    {
        $batchStatusesResponse = $this->getBatchStatusesService->getBatchStatuses();

        return $this->createSuccessResponse(
            $batchStatusesResponse['batchStatuses']
        );
    }
}
