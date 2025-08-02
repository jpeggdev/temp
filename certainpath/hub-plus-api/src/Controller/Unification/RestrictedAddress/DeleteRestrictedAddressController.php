<?php

namespace App\Controller\Unification\RestrictedAddress;

use App\Controller\ApiController;
use App\Service\Unification\DeleteUnificationRestrictedAddressService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class DeleteRestrictedAddressController extends ApiController
{
    public function __construct(
        private readonly DeleteUnificationRestrictedAddressService $service,
    ) {
    }

    #[Route(
        '/restricted-addresses/{id}',
        name: 'api_unification_restricted_address_delete',
        methods: ['DELETE']
    )]
    public function __invoke(
        int $id,
    ): Response {
        $this->service->deleteRestrictedAddress($id);

        return $this->createSuccessResponse([]);
    }
}
