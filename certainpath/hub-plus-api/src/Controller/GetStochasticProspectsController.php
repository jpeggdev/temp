<?php

namespace App\Controller;

use App\DTO\LoggedInUserDTO;
use App\DTO\Request\StochasticProspectQueryDTO;
use App\Exception\APICommunicationException;
use App\Service\Unification\GetProspectsService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;

#[Route(path: '/api/private')]
class GetStochasticProspectsController extends ApiController
{
    public function __construct(private readonly GetProspectsService $getProspectsService)
    {
    }

    /**
     * @throws APICommunicationException
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    #[Route('/stochastic-prospects', name: 'api_stochastic_prospects_get', methods: ['GET'])]
    public function __invoke(
        #[MapQueryString] StochasticProspectQueryDTO $queryDto,
        LoggedInUserDTO $loggedInUserDTO,
        Request $request,
    ): Response {
        $intacctId = $loggedInUserDTO->getActiveCompany()->getIntacctId();

        $prospectsResponse = $this->getProspectsService->getProspects($queryDto, $intacctId);

        return $this->createSuccessResponse(
            $prospectsResponse['prospects'],
            $prospectsResponse['totalCount']
        );
    }
}
