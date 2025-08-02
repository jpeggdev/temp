<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\Query\Export\GetBatchesProspectsCsvExportDTO;
use App\Service\Unification\ExportBatchesProspectsCsvService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;

#[Route(path: '/api/private')]
class ExportBatchesProspectsCsvController extends ApiController
{
    public function __construct(
        private readonly ExportBatchesProspectsCsvService $exportBatchProspectsCsvService,
    ) {
    }

    /**
     * @throws ClientExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     */
    #[Route('/batches/prospects/csv', name: 'api_batches_prospects_csv_get', methods: ['GET'])]
    public function __invoke(
        #[MapQueryString] GetBatchesProspectsCsvExportDTO $dto = new GetBatchesProspectsCsvExportDTO(),
    ): Response {
        return new StreamedResponse(function () use ($dto) {
            $this->exportBatchProspectsCsvService->exportBatchesProspectsCsv($dto);
        }, Response::HTTP_OK);
    }
}
