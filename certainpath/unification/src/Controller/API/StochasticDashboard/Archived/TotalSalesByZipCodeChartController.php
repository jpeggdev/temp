<?php

namespace App\Controller\API\StochasticDashboard\Archived;

use App\Controller\API\ApiController;
use App\DTO\Query\Chart\FilterableChartDTO;
use App\Exceptions\DomainException\StochasticDashboard\FailedToGetTotalSalesByZipCodeDataException;
use App\Services\StochasticDashboard\TotalSalesByZipCodeDataService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

class TotalSalesByZipCodeChartController extends ApiController
{
    public function __construct(
        private readonly TotalSalesByZipCodeDataService $chartService,
    ) {
    }

    /**
     * @throws FailedToGetTotalSalesByZipCodeDataException
     */
    #[Route(
        '/api/chart/total-sales-by-zip-code',
        name: 'api_chart_total_sales_by_zip_code_get',
        methods: ['GET']
    )]
    public function __invoke(
        #[MapQueryString] FilterableChartDTO $chartDTO = new FilterableChartDTO(),
    ): Response {
        $data = $this->chartService->getData($chartDTO);

        return $this->createJsonSuccessResponse($data);
    }
}
