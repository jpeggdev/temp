<?php

namespace App\Controller\API\Address;

use App\Controller\API\ApiController;
use App\Entity\RestrictedAddress;
use App\Services\Address\AddressService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DeleteRestrictedAddressController extends ApiController
{
    public function __construct(
        private readonly AddressService $addressService,
    ) {
    }

    #[Route(
        '/api/restrictedAddress/{id}/delete',
        name: 'api_restricted_address_delete',
        methods: ['DELETE']
    )]
    public function __invoke(
        RestrictedAddress $restrictedAddress,
    ): Response {
        $this->addressService
            ->deleteRestrictedAddress($restrictedAddress);

        return $this->createJsonSuccessResponse([]);
    }
}
