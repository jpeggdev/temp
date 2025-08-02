<?php

namespace App\Controller\API\FilterOption;

use App\Controller\API\ApiController;
use App\DTO\Query\FilterOption\GetYearFilterOptionsDTO;
use App\Services\ChartFilterOption\GetYearFilterOptionsService;
use Doctrine\DBAL\Exception;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

class GetYearFilterOptionsController extends ApiController
{
    public function __construct(
        private readonly GetYearFilterOptionsService $filterOptionsService,
    ) {
    }

    /**
     * @throws Exception
     */
    #[Route(
        '/api/filter-option/years',
        name: 'api_filter_option_years_get',
        methods: ['GET']
    )]
    public function __invoke(
        #[MapQueryString] GetYearFilterOptionsDTO $chartDTO = new GetYearFilterOptionsDTO()
    ): Response {
        $data = $this->filterOptionsService->getFilterOptions($chartDTO);

        return $this->createJsonSuccessResponse($data);
    }
}
