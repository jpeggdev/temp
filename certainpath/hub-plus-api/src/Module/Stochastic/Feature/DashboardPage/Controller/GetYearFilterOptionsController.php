<?php

declare(strict_types=1);

namespace App\Module\Stochastic\Feature\DashboardPage\Controller;

use App\Controller\ApiController;
use App\DTO\LoggedInUserDTO;
use App\Exception\APICommunicationException;
use App\Module\Stochastic\Feature\DashboardPage\DTO\Query\GetYearFilterOptionsDTO;
use App\Module\Stochastic\Feature\DashboardPage\Exception\FailedToGetYearFilterOptionsException;
use App\Module\Stochastic\Feature\DashboardPage\Service\GetYearFilterOptionsService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;

#[Route(path: '/api/private')]
class GetYearFilterOptionsController extends ApiController
{
    public function __construct(
        private readonly GetYearFilterOptionsService $getYearFilterOptionsService,
    ) {
    }

    /**
     * @throws APICommunicationException
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws FailedToGetYearFilterOptionsException
     */
    #[Route(
        '/filter-option/years',
        name: 'api_filter_option_years_get',
        methods: ['GET']
    )]
    public function __invoke(
        LoggedInUserDTO $loggedInUserDTO,
        #[MapQueryString] GetYearFilterOptionsDTO $chartDTO = new GetYearFilterOptionsDTO(),
    ): Response {
        $company = $loggedInUserDTO->getActiveCompany();
        $filterOptions = $this->getYearFilterOptionsService->getFilterOptions(
            $company->getIntacctId(),
            $chartDTO,
        );

        return $this->createSuccessResponse($filterOptions);
    }
}
