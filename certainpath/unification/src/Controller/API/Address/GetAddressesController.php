<?php

namespace App\Controller\API\Address;

use App\Controller\API\ApiController;
use App\DTO\Query\PaginationDTO;
use App\DTO\Request\Address\AddressQueryDTO;
use App\Repository\AddressRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

class GetAddressesController extends ApiController
{
    public function __construct(
        private readonly AddressRepository $addressRepository,
    ) {
    }

    #[Route('/api/addresses', name: 'api_addresses_get', methods: ['GET'])]
    public function __invoke(
        #[MapQueryString] AddressQueryDTO $queryDto = new AddressQueryDTO(),
        #[MapQueryString] PaginationDTO $paginationDto = new PaginationDTO(),
    ): Response {
        $addresses = $this->addressRepository
            ->findByAddressQueryDTO(
                $queryDto,
                $paginationDto
            )
            ->getArrayResult();

        $addressesCount = $this->addressRepository
            ->countByAddressQueryDTO($queryDto)
            ->getSingleScalarResult();

        $pagination['total'] = $addressesCount;
        $pagination['currentPage'] = $paginationDto->page;
        $pagination['perPage'] = $paginationDto->perPage;

        return $this->createJsonSuccessResponse($addresses, $pagination);
    }
}
