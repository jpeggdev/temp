<?php

namespace App\Controller;

use App\DTO\LoggedInUserDTO;
use App\DTO\Request\GetCompanyProspectsAggregatedRequestDTO;
use App\Exception\AggregatedProspectsRetrievalException;
use App\Service\Unification\GetCompanyProspectsAggregatedService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

#[Route(path: '/api/private')]
class GetCompanyProspectsAggregatedController extends ApiController
{
    public function __construct(
        private readonly GetCompanyProspectsAggregatedService $aggregatedService,
    ) {
    }

    /**
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws AggregatedProspectsRetrievalException
     * @throws TransportExceptionInterface
     */
    #[Route(
        '/company/aggregated-prospects',
        name: 'api_company_prospects_aggregated_get',
        methods: ['GET']
    )]
    public function __invoke(
        #[MapQueryString] GetCompanyProspectsAggregatedRequestDTO $requestDTO,
        LoggedInUserDTO $loggedInUserDTO,
        Request $request,
    ): Response {
        $aggregatedProspects = $this->aggregatedService->getAggregatedProspects(
            $loggedInUserDTO->getActiveCompany()->getIntacctId(),
            $requestDTO
        );

        return $this->createSuccessResponse($aggregatedProspects);
    }
}
