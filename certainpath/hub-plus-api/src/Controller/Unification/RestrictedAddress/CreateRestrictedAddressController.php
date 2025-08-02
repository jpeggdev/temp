<?php

namespace App\Controller\Unification\RestrictedAddress;

use App\Controller\ApiController;
use App\DTO\Request\RestrictedAddressCreateUpdateDTO;
use App\Service\Unification\CreateUnificationRestrictedAddressService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class CreateRestrictedAddressController extends ApiController
{
    public function __construct(
        private readonly CreateUnificationRestrictedAddressService $service,
    ) {
    }

    #[Route(
        '/restricted-addresses',
        name: 'api_unification_restricted_address_create',
        methods: ['POST']
    )]
    public function __invoke(
        #[MapRequestPayload] RestrictedAddressCreateUpdateDTO $editDTO,
    ): Response {
        $newAddress = $this->service->createRestrictedAddress($editDTO);

        return $this->createSuccessResponse($newAddress);
    }
}
