<?php

namespace App\Controller\API\Address;

use App\Controller\API\ApiController;
use App\DTO\Request\Address\RestrictedAddressEditDTO;
use App\Entity\RestrictedAddress;
use App\Exceptions\DomainException\RestrictedAddress\RestrictedAddressAlreadyExistsException;
use App\Services\Address\AddressService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

class CreateRestrictedAddressController extends ApiController
{
    public function __construct(
        private readonly AddressService $addressService,
    ) {
    }

    /**
     * @throws RestrictedAddressAlreadyExistsException
     */
    #[Route(
        '/api/restrictedAddress/create',
        name: 'api_restricted_address_create',
        methods: ['POST']
    )]
    public function __invoke(
        #[MapRequestPayload] RestrictedAddressEditDTO $restrictedAddressEditDTO,
    ): Response {
        $newRestrictedAddress = $this->addressService
            ->editRestrictedAddress(new RestrictedAddress(), $restrictedAddressEditDTO);

        return $this->createJsonSuccessResponse($newRestrictedAddress);
    }
}
