<?php

namespace App\Controller\API\FilterOption;

use App\Controller\API\ApiController;
use App\DTO\Query\FilterOption\GetCityFilterOptionsDTO;
use App\Services\ChartFilterOption\GetCityFilterOptionsService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

class GetCityFilterOptionsController extends ApiController
{
    public function __construct(
        private readonly GetCityFilterOptionsService $filterOptionsService,
    ) {
    }

    #[Route(
        '/api/filter-option/cities',
        name: 'api_filter_option_cities_get',
        methods: ['GET']
    )]
    public function __invoke(
        #[MapQueryString] GetCityFilterOptionsDTO $chartDTO = new GetCityFilterOptionsDTO()
    ): Response {
        $data = $this->filterOptionsService->getFilterOptions($chartDTO);

        return $this->createJsonSuccessResponse($data);
    }
}
