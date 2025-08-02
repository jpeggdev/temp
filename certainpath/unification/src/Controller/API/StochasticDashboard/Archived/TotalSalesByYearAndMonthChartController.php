<?php

namespace App\Controller\API\StochasticDashboard\Archived;

use App\Controller\API\ApiController;
use App\DTO\Query\Chart\FilterableChartDTO;
use App\Exceptions\DomainException\StochasticDashboard\FailedToGetTotalSalesByYearAndMonthDataException;
use App\Services\StochasticDashboard\TotalSalesByYearAndMonthDataService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

class TotalSalesByYearAndMonthChartController extends ApiController
{
    public function __construct(
        private readonly TotalSalesByYearAndMonthDataService $chartService,
    ) {
    }

    /**
     * @throws FailedToGetTotalSalesByYearAndMonthDataException
     */
    #[Route(
        '/api/chart/total-sales-by-year-and-month',
        name: 'api_chart_total_sales_by_year_and_month_get',
        methods: ['GET']
    )]
    public function __invoke(
        #[MapQueryString] FilterableChartDTO $chartDTO = new FilterableChartDTO()
    ): Response {
        $data = $this->chartService->getData($chartDTO);

        return $this->createJsonSuccessResponse($data);
    }
}
