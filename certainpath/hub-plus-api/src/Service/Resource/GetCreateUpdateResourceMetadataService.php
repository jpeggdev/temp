<?php

declare(strict_types=1);

namespace App\Service\Resource;

use App\DTO\Response\Resource\GetCreateUpdateResourceMetadataResponseDTO;
use App\Entity\EmployeeRole;
use App\Entity\ResourceCategory;
use App\Entity\ResourceTag;
use App\Entity\ResourceType;
use App\Entity\Trade;
use App\Repository\EmployeeRoleRepository;
use App\Repository\ResourceCategoryRepository;
use App\Repository\ResourceTagRepository;
use App\Repository\ResourceTypeRepository;
use App\Repository\TradeRepository;

final readonly class GetCreateUpdateResourceMetadataService
{
    public function __construct(
        private ResourceTagRepository $resourceTagRepo,
        private ResourceCategoryRepository $resourceCategoryRepo,
        private EmployeeRoleRepository $employeeRoleRepo,
        private TradeRepository $tradeRepo,
        private ResourceTypeRepository $resourceTypeRepo,
    ) {
    }

    public function getMetadata(): GetCreateUpdateResourceMetadataResponseDTO
    {
        $tags = $this->resourceTagRepo->findAll();
        $categories = $this->resourceCategoryRepo->findAll();
        $roles = $this->employeeRoleRepo->findAll();
        $trades = $this->tradeRepo->findAll();
        $resourceTypes = $this->resourceTypeRepo->findBy([], ['sortOrder' => 'ASC']);

        $mappedTags = array_map(
            fn (ResourceTag $tag) => [
                'id' => $tag->getId(),
                'name' => $tag->getName(),
            ],
            $tags
        );

        $mappedCategories = array_map(
            fn (ResourceCategory $cat) => [
                'id' => $cat->getId(),
                'name' => $cat->getName(),
            ],
            $categories
        );

        $mappedRoles = array_map(
            fn (EmployeeRole $role) => [
                'id' => $role->getId(),
                'name' => $role->getName(),
            ],
            $roles
        );

        $mappedTrades = array_map(
            fn (Trade $trade) => [
                'id' => $trade->getId(),
                'name' => $trade->getName(),
            ],
            $trades
        );

        $mappedResourceTypes = array_map(
            fn (ResourceType $type) => [
                'id' => $type->getId(),
                'name' => $type->getName(),
                'requiresContentUrl' => $type->isRequiresContentUrl(),
                'isDefault' => $type->isDefault(),
            ],
            $resourceTypes
        );

        return new GetCreateUpdateResourceMetadataResponseDTO(
            resourceTags: $mappedTags,
            resourceCategories: $mappedCategories,
            employeeRoles: $mappedRoles,
            trades: $mappedTrades,
            resourceTypes: $mappedResourceTypes
        );
    }
}
