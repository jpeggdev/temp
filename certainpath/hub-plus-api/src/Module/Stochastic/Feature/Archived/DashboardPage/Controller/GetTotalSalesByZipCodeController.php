<?php

declare(strict_types=1);

namespace App\Module\Stochastic\Feature\Archived\DashboardPage\Controller;

use App\Controller\ApiController;
use App\DTO\LoggedInUserDTO;
use App\Exception\APICommunicationException;
use App\Module\Stochastic\Feature\Archived\DashboardPage\DTO\Query\GetTotalSalesByZipCodeChartDTO;
use App\Module\Stochastic\Feature\Archived\DashboardPage\Exception\FailedToGetTotalSalesByZipCodeChartDataException;
use App\Module\Stochastic\Feature\Archived\DashboardPage\Service\GetTotalSalesByZipCodeChartDataService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;

#[Route(path: '/api/private')]
class GetTotalSalesByZipCodeController extends ApiController
{
    public function __construct(
        private readonly GetTotalSalesByZipCodeChartDataService $chartService,
    ) {
    }

    /**
     * @throws APICommunicationException
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws FailedToGetTotalSalesByZipCodeChartDataException
     */
    #[Route(
        '/chart/total-sales-by-zip-code',
        name: 'api_chart_total_sales_by_zip_code_get',
        methods: ['GET'],
    )]
    public function __invoke(
        LoggedInUserDTO $loggedInUserDTO,
        #[MapQueryString] GetTotalSalesByZipCodeChartDTO $queryDTO = new GetTotalSalesByZipCodeChartDTO(),
    ): Response {
        $company = $loggedInUserDTO->getActiveCompany();
        $chartData = $this->chartService->getChartData(
            $company->getIntacctId(),
            $queryDTO
        );

        return $this->createSuccessResponse($chartData);
    }
}
