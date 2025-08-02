<?php

namespace App\Repository;

use App\DTO\Query\Location\LocationsQueryDTO;
use App\DTO\Query\PaginationDTO;
use App\Entity\Location;
use App\Exceptions\NotFoundException\LocationNotFoundException;
use App\QueryBuilder\LocationQueryBuilder;
use App\Services\PaginatorService;
use Doctrine\Persistence\ManagerRegistry;

class LocationRepository extends AbstractRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly PaginatorService $paginator,
        private readonly LocationQueryBuilder $locationQueryBuilder,

    )
    {
        parent::__construct($registry, Location::class);
    }

    public function saveLocation(Location $campaign): Location
    {
        /** @var Location $saved */
        $saved = $this->save($campaign);
        return $saved;
    }

    public function findOneById(int $id): ?Location
    {
        return $this->locationQueryBuilder
            ->createFindOneByIdQueryBuilder($id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @throws LocationNotFoundException
     */
    public function findOneByIdOrFail(int $id): Location
    {
        $location = $this->findOneById($id);
        if (!$location) {
            throw new LocationNotFoundException();
        }

        return $location;
    }

    public function findOneByName(string $name): ?Location
    {
        return $this->locationQueryBuilder
            ->createFindOneByNameQueryBuilder($name)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function paginateAll(
        PaginationDTO $paginationDTO,
        LocationsQueryDTO $locationsQueryDTO,
    ): array {
        $sortBy = $paginationDTO->sortBy;
        $sortOrder = $paginationDTO->sortOrder;
        $page = $paginationDTO->page;
        $perPage = $paginationDTO->perPage;

        $query = $this->locationQueryBuilder
            ->createFetchAllByDTOQueryBuilder($locationsQueryDTO)
            ->orderBy("l.$sortBy", $sortOrder);

        return $this->paginator->paginate($query, $page, $perPage);
    }
}
