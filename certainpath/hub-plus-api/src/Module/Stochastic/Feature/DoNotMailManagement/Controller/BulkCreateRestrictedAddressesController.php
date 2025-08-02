<?php

namespace App\Module\Stochastic\Feature\DoNotMailManagement\Controller;

use App\Controller\ApiController;
use App\Module\Stochastic\Feature\DoNotMailManagement\DTO\BulkCreateRestrictedAddressesDTO;
use App\Module\Stochastic\Feature\DoNotMailManagement\Service\BulkCreateRestrictedAddressesService;
use App\Module\Stochastic\Feature\DoNotMailManagement\Voter\BulkRestrictedAddressVoter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

#[Route(path: '/api/private')]
class BulkCreateRestrictedAddressesController extends ApiController
{
    public function __construct(
        private readonly BulkCreateRestrictedAddressesService $service,
    ) {
    }

    /**
     * @throws ServerExceptionInterface
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws TransportExceptionInterface
     * @throws RedirectionExceptionInterface
     */
    #[Route(
        '/restricted-addresses/bulk-create',
        name: 'api_restricted_addresses_bulk_create',
        methods: ['POST']
    )]
    public function __invoke(
        #[MapRequestPayload] BulkCreateRestrictedAddressesDTO $requestDTO,
    ): Response {
        $this->denyAccessUnlessGranted(BulkRestrictedAddressVoter::CREATE);
        $result = $this->service->bulkCreate($requestDTO);

        return $this->createSuccessResponse($result);
    }
}
