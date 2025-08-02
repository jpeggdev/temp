<?php

namespace App\Controller\API\RestrictedAddress;

use App\Controller\API\ApiController;
use App\DTO\Request\RestrictedAddress\BulkCreateRestrictedAddressesDTO;
use App\Exceptions\DomainException\RestrictedAddress\RestrictedAddressAlreadyExistsException;
use App\Services\RestrictedAddress\BulkCreateRestrictedAddressesService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

class BulkCreateRestrictedAddressesController extends ApiController
{
    public function __construct(
        private readonly BulkCreateRestrictedAddressesService $bulkCreateRestrictedAddressesService,
    ) {
    }

    /**
     * @throws RestrictedAddressAlreadyExistsException
     */
    #[Route(
        '/api/restricted-address/bulk-create',
        name: 'api_restricted_address_bulk_create',
        methods: ['POST']
    )]
    public function __invoke(
        #[MapRequestPayload] BulkCreateRestrictedAddressesDTO $dto,
    ): Response {
        $result = $this->bulkCreateRestrictedAddressesService->bulkCreate($dto);

        return $this->createJsonSuccessResponse($result);
    }
}
