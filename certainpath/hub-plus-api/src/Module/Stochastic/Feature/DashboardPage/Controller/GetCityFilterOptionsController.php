<?php

declare(strict_types=1);

namespace App\Module\Stochastic\Feature\DashboardPage\Controller;

use App\Controller\ApiController;
use App\DTO\LoggedInUserDTO;
use App\Exception\APICommunicationException;
use App\Module\Stochastic\Feature\DashboardPage\DTO\Query\GetCityFilterOptionsDTO;
use App\Module\Stochastic\Feature\DashboardPage\Exception\FailedToGetCityFilterOptionsException;
use App\Module\Stochastic\Feature\DashboardPage\Service\GetCityFilterOptionsService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;

#[Route(path: '/api/private')]
class GetCityFilterOptionsController extends ApiController
{
    public function __construct(
        private readonly GetCityFilterOptionsService $getCityFilterOptionsService,
    ) {
    }

    /**
     * @throws APICommunicationException
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws FailedToGetCityFilterOptionsException
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     */
    #[Route(
        '/filter-option/cities',
        name: 'api_filter_option_cities_get',
        methods: ['GET']
    )]
    public function __invoke(
        LoggedInUserDTO $loggedInUserDTO,
        #[MapQueryString] GetCityFilterOptionsDTO $chartDTO = new GetCityFilterOptionsDTO(),
    ): Response {
        $company = $loggedInUserDTO->getActiveCompany();
        $filterOptions = $this->getCityFilterOptionsService->getFilterOptions(
            $company->getIntacctId(),
            $chartDTO,
        );

        return $this->createSuccessResponse($filterOptions);
    }
}
