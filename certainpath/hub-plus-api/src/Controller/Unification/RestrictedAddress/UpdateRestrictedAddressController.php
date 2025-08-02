<?php

namespace App\Controller\Unification\RestrictedAddress;

use App\Controller\ApiController;
use App\DTO\Request\RestrictedAddressCreateUpdateDTO;
use App\Service\Unification\UpdateUnificationRestrictedAddressService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class UpdateRestrictedAddressController extends ApiController
{
    public function __construct(
        private readonly UpdateUnificationRestrictedAddressService $service,
    ) {
    }

    #[Route(
        '/restricted-addresses/{id}',
        name: 'api_unification_restricted_address_update',
        methods: ['PUT']
    )]
    public function __invoke(
        int $id,
        #[MapRequestPayload] RestrictedAddressCreateUpdateDTO $editDTO,
    ): Response {
        $updatedAddress = $this->service->updateRestrictedAddress($id, $editDTO);

        return $this->createSuccessResponse($updatedAddress);
    }
}
