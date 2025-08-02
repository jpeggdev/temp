<?php

namespace App\Controller\API\RestrictedAddress;

use App\Controller\API\ApiController;
use App\DTO\Request\Address\RestrictedAddressEditDTO;
use App\Entity\RestrictedAddress;
use App\Services\Address\AddressService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

class EditRestrictedAddressController extends ApiController
{
    public function __construct(
        private readonly AddressService $addressService,
    ) {
    }

    #[Route(
        '/api/restrictedAddress/{id}/edit',
        name: 'api_restricted_address_edit',
        methods: ['PUT']
    )]
    public function __invoke(
        RestrictedAddress $restrictedAddress,
        #[MapRequestPayload] RestrictedAddressEditDTO $restrictedAddressEditDTO,
    ): Response {
        $updatedRestrictedAddress = $this->addressService
            ->editRestrictedAddress($restrictedAddress, $restrictedAddressEditDTO);

        return $this->createJsonSuccessResponse($updatedRestrictedAddress);
    }
}
