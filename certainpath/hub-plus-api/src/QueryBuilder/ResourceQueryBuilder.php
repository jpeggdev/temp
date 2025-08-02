<?php

declare(strict_types=1);

namespace App\QueryBuilder;

use App\DTO\Request\Resource\GetResourceSearchResultsQueryDTO;
use App\DTO\Request\Resource\GetResourcesRequestDTO;
use App\Entity\Employee;
use App\Entity\Resource;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;

readonly class ResourceQueryBuilder
{
    public function __construct(
        private EntityManagerInterface $em,
    ) {
    }

    public function createBaseQueryBuilder(): QueryBuilder
    {
        return $this->em->createQueryBuilder()
            ->select('r')
            ->from(Resource::class, 'r');
    }

    public function createBaseCountQueryBuilder(): QueryBuilder
    {
        return $this->em->createQueryBuilder()
            ->select('COUNT(r.id)')
            ->from(Resource::class, 'r');
    }

    public function createFindResourcesByQueryBuilder(GetResourcesRequestDTO $queryDto): QueryBuilder
    {
        $qb = $this->createBaseQueryBuilder();

        $qb->setMaxResults($queryDto->pageSize)
            ->setFirstResult(($queryDto->page - 1) * $queryDto->pageSize)
            ->orderBy('r.'.$queryDto->sortBy, $queryDto->sortOrder);

        $this->applyFilters($qb, $queryDto);

        return $qb;
    }

    public function createCountResourcesByQueryBuilder(GetResourcesRequestDTO $queryDto): QueryBuilder
    {
        $qb = $this->createBaseCountQueryBuilder();

        $this->applyFilters($qb, $queryDto);

        return $qb;
    }

    public function createFindPublishedResourcesByQueryBuilder(
        GetResourceSearchResultsQueryDTO $queryDto,
        ?Employee $employee = null,
    ): QueryBuilder {
        $qb = $this->createBaseQueryBuilder();

        $qb->setMaxResults($queryDto->pageSize)
            ->setFirstResult(($queryDto->page - 1) * $queryDto->pageSize);

        if (!$queryDto->sortBy) {
            $qb
                ->addSelect('COALESCE(r.publishStartDate, r.createdAt) AS HIDDEN sortDate')
                ->orderBy('sortDate', 'DESC');
        } else {
            $qb->orderBy('r.'.$queryDto->sortBy, $queryDto->sortOrder);
        }

        $this->applyPublishedWindowFilter($qb);

        $this->applyFilters($qb, $queryDto, $employee);

        return $qb;
    }

    public function createCountPublishedResourcesByQueryBuilder(
        GetResourceSearchResultsQueryDTO $queryDto,
        ?Employee $employee = null,
    ): QueryBuilder {
        $qb = $this->createBaseCountQueryBuilder();

        $this->applyPublishedWindowFilter($qb);

        $this->applyFilters($qb, $queryDto, $employee);

        return $qb;
    }

    private function applyFilters(QueryBuilder $qb, object $dto, ?Employee $employee = null): void
    {
        if (property_exists($dto, 'searchTerm') && $dto->searchTerm) {
            $this->applySearchTermFilter($qb, $dto->searchTerm);
        }

        if (property_exists($dto, 'tradeIds') && $dto->tradeIds && \count($dto->tradeIds) > 0) {
            $this->applyTradeIdsFilter($qb, $dto->tradeIds);
        }

        if (property_exists($dto, 'categoryIds') && $dto->categoryIds && \count($dto->categoryIds) > 0) {
            $this->applyCategoryIdsFilter($qb, $dto->categoryIds);
        }

        if (property_exists($dto, 'employeeRoleIds') && $dto->employeeRoleIds && \count($dto->employeeRoleIds) > 0) {
            $this->applyEmployeeRoleIdsFilter($qb, $dto->employeeRoleIds);
        }

        if (property_exists($dto, 'tagIds') && $dto->tagIds && \count($dto->tagIds) > 0) {
            $this->applyTagIdsFilter($qb, $dto->tagIds);
        }

        if (
            property_exists($dto, 'resourceTypeIds')
            && $dto->resourceTypeIds
            && \count($dto->resourceTypeIds) > 0
        ) {
            $this->applyResourceTypeIdsFilter($qb, $dto->resourceTypeIds);
        }

        if (
            property_exists($dto, 'contentUrl')
            && $dto->contentUrl
            && str_starts_with($dto->contentUrl, 'https://cpfiles-public.s3.amazonaws.com/cpfiles-public/file_')
        ) {
            $this->applyContentUrlFilter($qb, $dto->contentUrl);
        }

        if (
            property_exists($dto, 'showFavorites')
            && true === $dto->showFavorites
            && null !== $employee
        ) {
            $this->applyShowFavoritesFilter($qb, $employee);
        }
    }

    private function applySearchTermFilter(QueryBuilder $qb, string $searchTerm): void
    {
        $qb
            ->andWhere('TSMATCH(r.searchVector, WEBCSEARCH_TO_TSQUERY(\'english\', :searchTerm)) = true')
            ->setParameter('searchTerm', $searchTerm);
    }

    private function applyTradeIdsFilter(QueryBuilder $qb, array $tradeIds): void
    {
        $qb
            ->innerJoin('r.resourceTradeMappings', 'rtm')
            ->andWhere('rtm.trade IN (:tradeIds)')
            ->setParameter('tradeIds', $tradeIds);
    }

    private function applyCategoryIdsFilter(QueryBuilder $qb, array $categoryIds): void
    {
        $qb
            ->innerJoin('r.resourceCategoryMappings', 'rcm')
            ->andWhere('rcm.resourceCategory IN (:categoryIds)')
            ->setParameter('categoryIds', $categoryIds);
    }

    private function applyEmployeeRoleIdsFilter(QueryBuilder $qb, array $employeeRoleIds): void
    {
        $qb
            ->innerJoin('r.resourceEmployeeRoleMappings', 'rerm')
            ->andWhere('rerm.employeeRole IN (:employeeRoleIds)')
            ->setParameter('employeeRoleIds', $employeeRoleIds);
    }

    private function applyTagIdsFilter(QueryBuilder $qb, array $tagIds): void
    {
        $qb
            ->innerJoin('r.resourceTagMappings', 'rtag')
            ->andWhere('rtag.resourceTag IN (:tagIds)')
            ->setParameter('tagIds', $tagIds);
    }

    private function applyResourceTypeIdsFilter(QueryBuilder $qb, array $resourceTypeIds): void
    {
        $qb
            ->andWhere('r.type IN (:resourceTypeIds)')
            ->setParameter('resourceTypeIds', $resourceTypeIds);
    }

    private function applyContentUrlFilter(QueryBuilder $qb, string $contentUrl): void
    {
        $qb
            ->innerJoin('r.file', 'f')
            ->andWhere('f.url = :contentUrl')
            ->addSelect('f.original_filename as contentFilename')
            ->setParameter('contentUrl', $contentUrl);
    }

    private function applyShowFavoritesFilter(QueryBuilder $qb, Employee $employee): void
    {
        $qb
            ->innerJoin('r.resourceFavorites', 'rf')
            ->andWhere('rf.employee = :employeeId')
            ->setParameter('employeeId', $employee->getId());
    }

    private function applyPublishedWindowFilter(QueryBuilder $qb): void
    {
        $qb->andWhere('r.isPublished = true')
            ->andWhere('(r.publishStartDate IS NULL OR r.publishStartDate <= :now)')
            ->andWhere('(r.publishEndDate IS NULL OR r.publishEndDate >= :now)')
            ->setParameter('now', new \DateTimeImmutable());
    }
}
