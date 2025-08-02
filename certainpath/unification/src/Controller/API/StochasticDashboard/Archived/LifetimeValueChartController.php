<?php

namespace App\Controller\API\StochasticDashboard\Archived;

use App\Controller\API\ApiController;
use App\DTO\Query\Chart\FilterableChartDTO;
use App\Exceptions\DomainException\StochasticDashboard\FailedToLifetimeValueDataException;
use App\Services\StochasticDashboard\LifetimeValueDataService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

class LifetimeValueChartController extends ApiController
{
    public function __construct(
        private readonly LifetimeValueDataService $chartService,
    ) {
    }

    /**
     * @throws FailedToLifetimeValueDataException
     */
    #[Route(
        '/api/chart/lifetime-value',
        name: 'api_chart_lifetime_value_get',
        methods: ['GET']
    )]
    public function __invoke(
        #[MapQueryString] FilterableChartDTO $chartDTO = new FilterableChartDTO()
    ): Response {
        $data = $this->chartService->getData($chartDTO);

        return $this->createJsonSuccessResponse($data);
    }
}
