<?php

declare(strict_types=1);

namespace App\Module\Stochastic\Feature\DashboardPage\Controller;

use App\Controller\ApiController;
use App\DTO\LoggedInUserDTO;
use App\Exception\APICommunicationException;
use App\Module\Stochastic\Feature\DashboardPage\DTO\Query\GetTradeFilterOptionsDTO;
use App\Module\Stochastic\Feature\DashboardPage\Exception\FailedToGetTradeFilterOptionsException;
use App\Module\Stochastic\Feature\DashboardPage\Service\GetTradeFilterOptionsService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;

#[Route(path: '/api/private')]
class GetTradeFilterOptionsController extends ApiController
{
    public function __construct(
        private readonly GetTradeFilterOptionsService $getTradeFilterOptionsService,
    ) {
    }

    /**
     * @throws APICommunicationException
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws FailedToGetTradeFilterOptionsException
     */
    #[Route(
        '/filter-option/trades',
        name: 'api_filter_option_trades_get',
        methods: ['GET']
    )]
    public function __invoke(
        LoggedInUserDTO $loggedInUserDTO,
        #[MapQueryString] GetTradeFilterOptionsDTO $chartDTO = new GetTradeFilterOptionsDTO(),
    ): Response {
        $company = $loggedInUserDTO->getActiveCompany();
        $filterOptions = $this->getTradeFilterOptionsService->getFilterOptions($chartDTO);

        return $this->createSuccessResponse($filterOptions);
    }
}
