<?php

namespace App\Controller;

use App\Service\Unification\GetUnificationRestrictedAddressService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class GetUnificationRestrictedAddressController extends ApiController
{
    public function __construct(
        private readonly GetUnificationRestrictedAddressService $service,
    ) {
    }

    #[Route(
        '/restricted-addresses/{id}',
        name: 'api_unification_restricted_address_get_one',
        methods: ['GET']
    )]
    public function __invoke(
        int $id,
    ): Response {
        // Call the service to fetch one address from Unification
        $addressDto = $this->service->getRestrictedAddress($id);

        return $this->createSuccessResponse($addressDto);
    }
}
