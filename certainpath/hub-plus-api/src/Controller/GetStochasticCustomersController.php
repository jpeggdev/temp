<?php

namespace App\Controller;

use App\DTO\LoggedInUserDTO;
use App\DTO\Request\StochasticCustomerQueryDTO;
use App\Exception\APICommunicationException;
use App\Service\Unification\GetCustomersService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;

#[Route(path: '/api/private')]
class GetStochasticCustomersController extends ApiController
{
    public function __construct(private readonly GetCustomersService $getCustomersService)
    {
    }

    /**
     * @throws APICommunicationException
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    #[Route('/stochastic-customers', name: 'api_stochastic_customers_get', methods: ['GET'])]
    public function __invoke(
        #[MapQueryString] StochasticCustomerQueryDTO $queryDto,
        LoggedInUserDTO $loggedInUserDTO,
        Request $request,
    ): Response {
        $intacctId = $loggedInUserDTO->getActiveCompany()->getIntacctId();

        $customersResponse = $this->getCustomersService->getCustomers($queryDto, $intacctId);

        return $this->createSuccessResponse(
            $customersResponse['customers'],
            $customersResponse['totalCount']
        );
    }
}
