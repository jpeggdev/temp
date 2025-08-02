<?php

namespace App\Controller\API\RestrictedAddress;

use App\Controller\API\ApiController;
use App\DTO\Query\PaginationDTO;
use App\DTO\Request\Address\RestrictedAddressQueryDTO;
use App\Repository\RestrictedAddressRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

class GetRestrictedAddressesController extends ApiController
{
    public function __construct(
        private readonly RestrictedAddressRepository $restrictedAddressRepository,
    ) {
    }

    #[Route('/api/restrictedAddresses', name: 'api_restricted_addresses_get', methods: ['GET'])]
    public function __invoke(
        #[MapQueryString] RestrictedAddressQueryDTO $queryDto = new RestrictedAddressQueryDTO(),
        #[MapQueryString] PaginationDTO $paginationDto = new PaginationDTO(),
    ): Response {
        $restrictedAddresses = $this->restrictedAddressRepository
            ->findByRestrictedAddressQueryDTO(
                $queryDto,
                $paginationDto
            )
            ->getArrayResult();

        $restrictedAddressesCount = $this->restrictedAddressRepository
            ->countByRestrictedAddressQueryDTO($queryDto)
            ->getSingleScalarResult();

        $pagination['total'] = $restrictedAddressesCount;
        $pagination['currentPage'] = $paginationDto->page;
        $pagination['perPage'] = $paginationDto->perPage;

        return $this->createJsonSuccessResponse($restrictedAddresses, $pagination);
    }
}
