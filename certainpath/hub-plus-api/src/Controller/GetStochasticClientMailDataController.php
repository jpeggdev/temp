<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\Request\StochasticClientMailDataQueryDTO;
use App\DTO\Response\StochasticClientMailDataRowDTO;
use App\Exception\APICommunicationException;
use App\Service\Unification\GetStochasticClientMailDataService;
use App\ValueObject\StochasticClientMailDataTabularStream;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;

/**
 * Example route: GET /api/private/stochastic-client-mail-data?week=2&year=2026&page=1&perPage=10&sortOrder=ASC.
 */
#[Route(path: '/api/private')]
class GetStochasticClientMailDataController extends ApiController
{
    public function __construct(
        private readonly GetStochasticClientMailDataService $getStochasticClientDataService,
    ) {
    }

    /**
     * @throws APICommunicationException
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    #[Route(
        '/stochastic-client-mail-data',
        name: 'api_stochastic_client_mail_data',
        methods: ['GET']
    )]
    public function __invoke(
        #[MapQueryString] StochasticClientMailDataQueryDTO $queryDTO,
    ): Response {
        $week = $queryDTO->week;
        $year = $queryDTO->year;
        $page = $queryDTO->page;
        $perPage = $queryDTO->perPage;
        $sortOrder = $queryDTO->sortOrder;
        $serviceResult = $this->getStochasticClientDataService->getStochasticMailData(
            week: $week,
            year: $year,
            page: $page,
            perPage: $queryDTO->isCsv ? 1000 : $perPage,
            sortOrder: $sortOrder
        );

        /** @var StochasticClientMailDataRowDTO[]  $mailDataRows */
        $mailDataRows = $serviceResult['mailDataRows'];

        if ($queryDTO->isCsv) {
            return $this->createCsvStreamedResponse(
                sprintf('stochastic-client-mail-data-week-%d-year-%d.csv', $week, $year),
                StochasticClientMailDataTabularStream::fromDtoArray(
                    $mailDataRows
                )->asGenerator()
            );
        }

        return $this->createSuccessResponse(
            data: $mailDataRows,
            totalCount: $serviceResult['totalCount']
        );
    }
}
