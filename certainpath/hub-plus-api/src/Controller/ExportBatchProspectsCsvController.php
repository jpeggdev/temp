<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\Unification\ExportBatchProspectsCsvService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;

#[Route(path: '/api/private')]
class ExportBatchProspectsCsvController extends ApiController
{
    public function __construct(
        private readonly ExportBatchProspectsCsvService $exportBatchProspectsCsvService,
    ) {
    }

    /**
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     */
    #[Route('/batch/{id}/prospects/csv', name: 'api_batch_prospects_csv_get', methods: ['GET'])]
    public function __invoke(int $id): Response
    {
        return new StreamedResponse(function () use ($id) {
            $this->exportBatchProspectsCsvService->exportBatchProspectsCsv($id);
        }, Response::HTTP_OK);
    }
}
