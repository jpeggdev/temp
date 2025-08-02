<?php

namespace App\Controller\API\FilterOption;

use App\Controller\API\ApiController;
use App\DTO\Query\FilterOption\GetCityFilterOptionsDTO;
use App\DTO\Query\FilterOption\GetTradeFilterOptionsDTO;
use App\Services\ChartFilterOption\GetTradeFilterOptionsService;
use Doctrine\DBAL\Exception;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

class GetTradeFilterOptionsController extends ApiController
{
    public function __construct(
        private readonly GetTradeFilterOptionsService $filterOptionsService,
    ) {
    }

    #[Route(
        '/api/filter-option/trades',
        name: 'api_filter_option_trades_get',
        methods: ['GET']
    )]
    public function __invoke(
        #[MapQueryString] GetTradeFilterOptionsDTO $chartDTO = new GetTradeFilterOptionsDTO()
    ): Response {
        $data = $this->filterOptionsService->getFilterOptions($chartDTO);

        return $this->createJsonSuccessResponse($data);
    }
}
