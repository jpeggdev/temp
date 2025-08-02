<?php

namespace App\Controller\API\Address;

use App\Controller\API\ApiController;
use App\DTO\Request\RestrictedAddress\GetAddressesMatchesDTO;
use App\Services\Address\GetAddressesMatchesService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

class GetAddressesMatchesController extends ApiController
{
    public function __construct(
        private readonly GetAddressesMatchesService $getAddressesMatchesService,
    ) {
    }

    #[Route(
        '/api/addresses/matches',
        name: 'api_addresses_matches',
        methods: ['POST']
    )]
    public function __invoke(
        #[MapRequestPayload] GetAddressesMatchesDTO $dto,
    ): Response {
        $addresses = $dto->addresses;
        $addressesMatchesData = $this->getAddressesMatchesService->getMatches($addresses);

        return $this->createJsonSuccessResponse($addressesMatchesData);
    }
}
