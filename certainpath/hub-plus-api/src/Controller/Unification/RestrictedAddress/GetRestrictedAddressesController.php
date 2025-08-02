<?php

namespace App\Controller\Unification\RestrictedAddress;

use App\Controller\ApiController;
use App\DTO\Request\RestrictedAddressQueryDTO;
use App\Exception\APICommunicationException;
use App\Service\Unification\GetUnificationRestrictedAddressesService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class GetRestrictedAddressesController extends ApiController
{
    public function __construct(
        private readonly GetUnificationRestrictedAddressesService $service,
    ) {
    }

    /**
     * @throws APICommunicationException
     */
    #[Route(
        '/restricted-addresses',
        name: 'api_unification_restricted_addresses_get',
        methods: ['GET']
    )]
    public function __invoke(
        #[MapQueryString] RestrictedAddressQueryDTO $queryDto,
    ): Response {
        $responseData = $this->service->getRestrictedAddresses($queryDto);

        return $this->createSuccessResponse(
            $responseData['addresses'],
            $responseData['total']
        );
    }
}
