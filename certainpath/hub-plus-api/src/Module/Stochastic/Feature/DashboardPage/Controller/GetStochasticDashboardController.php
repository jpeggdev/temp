<?php

declare(strict_types=1);

namespace App\Module\Stochastic\Feature\DashboardPage\Controller;

use App\Controller\ApiController;
use App\DTO\LoggedInUserDTO;
use App\Exception\APICommunicationException;
use App\Module\Stochastic\Feature\DashboardPage\DTO\Query\GetStochasticDashboardDTO;
use App\Module\Stochastic\Feature\DashboardPage\Exception\FailedToGetStochasticDashboardDataException;
use App\Module\Stochastic\Feature\DashboardPage\Service\GetStochasticDashboardChartsService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;

#[Route(path: '/api/private')]
class GetStochasticDashboardController extends ApiController
{
    public function __construct(
        private readonly GetStochasticDashboardChartsService $getStochasticDashboardDataService,
    ) {
    }

    /**
     * @throws APICommunicationException
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws FailedToGetStochasticDashboardDataException
     */
    #[Route(
        '/stochastic/dashboard',
        name: 'api_stochastic_dashboard_get',
        methods: ['GET']
    )]
    public function __invoke(
        LoggedInUserDTO $loggedInUserDTO,
        #[MapQueryString] GetStochasticDashboardDTO $queryDto = new GetStochasticDashboardDTO(),
    ): Response {
        $company = $loggedInUserDTO->getActiveCompany();
        $data = $this->getStochasticDashboardDataService->getData(
            $company->getIntacctId(),
            $queryDto,
        );

        return $this->createSuccessResponse($data);
    }
}
