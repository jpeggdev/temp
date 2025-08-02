<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\LoggedInUserDTO;
use App\DTO\Request\ArchiveBatchDTO;
use App\Exception\BatchArchiveException;
use App\Service\Unification\ArchiveBatchService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

#[Route(path: '/api/private')]
class ArchiveBatchController extends ApiController
{
    public function __construct(
        private readonly ArchiveBatchService $archiveBatchService,
    ) {
    }

    /**
     * @throws BatchArchiveException
     * @throws ServerExceptionInterface
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws TransportExceptionInterface
     * @throws RedirectionExceptionInterface
     */
    #[Route('/batch/archive', name: 'api_batch_archive', methods: ['PATCH'])]
    public function __invoke(
        #[MapRequestPayload] ArchiveBatchDTO $archiveBatchDTO,
        LoggedInUserDTO $loggedInUserDTO,
    ): Response {
        $this->archiveBatchService->archiveBatch($archiveBatchDTO->batchId);

        return $this->createSuccessResponse([
            'message' => sprintf('Batch %d has been archived.', $archiveBatchDTO->batchId),
        ]);
    }
}
