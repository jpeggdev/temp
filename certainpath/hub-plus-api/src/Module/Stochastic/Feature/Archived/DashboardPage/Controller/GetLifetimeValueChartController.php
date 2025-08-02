<?php

declare(strict_types=1);

namespace App\Module\Stochastic\Feature\Archived\DashboardPage\Controller;

use App\Controller\ApiController;
use App\DTO\LoggedInUserDTO;
use App\Exception\APICommunicationException;
use App\Module\Stochastic\Feature\Archived\DashboardPage\DTO\Query\GetLifetimeValueChartDTO;
use App\Module\Stochastic\Feature\Archived\DashboardPage\Exception\FailedToGetLifetimeValueChartDataException;
use App\Module\Stochastic\Feature\DashboardPage\Service\GetLifetimeValueChartDataService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;

#[Route(path: '/api/private')]
class GetLifetimeValueChartController extends ApiController
{
    public function __construct(
        private readonly GetLifetimeValueChartDataService $getLifetimeValueChartDataService,
    ) {
    }

    /**
     * @throws APICommunicationException
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws FailedToGetLifetimeValueChartDataException
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     */
    #[Route(
        '/chart/lifetime-value',
        name: 'api_chart_lifetime_value_get',
        methods: ['GET']
    )]
    public function __invoke(
        LoggedInUserDTO $loggedInUserDTO,
        #[MapQueryString] GetLifetimeValueChartDTO $chartDTO = new GetLifetimeValueChartDTO(),
    ): Response {
        $company = $loggedInUserDTO->getActiveCompany();

        $chartData = $this->getLifetimeValueChartDataService->getChartData(
            $company->getIntacctId(),
            $chartDTO,
        );

        return $this->createSuccessResponse($chartData);
    }
}
