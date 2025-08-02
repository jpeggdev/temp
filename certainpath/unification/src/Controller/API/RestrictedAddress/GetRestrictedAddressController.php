<?php

namespace App\Controller\API\RestrictedAddress;

use App\Controller\API\ApiController;
use App\DTO\Domain\RestrictedAddressDTO;
use App\Entity\RestrictedAddress;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class GetRestrictedAddressController extends ApiController
{
    #[Route(
        '/api/restrictedAddress/{id}',
        name: 'api_restricted_address_get_one',
        methods: ['GET']
    )]
    public function __invoke(
        RestrictedAddress $restrictedAddress
    ): Response {
        $dto = RestrictedAddressDTO::fromEntity($restrictedAddress);
        return $this->createJsonSuccessResponse($dto);
    }
}
