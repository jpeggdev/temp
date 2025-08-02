<?php

declare(strict_types=1);

namespace App\Module\Hub\Feature\ResourceLibrary\Service;

use App\DTO\Response\EmployeeRole\GetEmployeeRolesResponseDTO;
use App\DTO\Response\ResourceCategory\GetResourceCategoriesResponseDTO;
use App\DTO\Response\Trade\GetTradeResponseDTO;
use App\Module\Hub\Feature\ResourceLibrary\DTO\GetResourceTypeResponseDTO;
use App\Repository\EmployeeRoleRepository;
use App\Repository\ResourceCategoryRepository;
use App\Repository\ResourceTypeRepository;
use App\Repository\TradeRepository;

final readonly class GetResourceLibraryMetadataService
{
    public function __construct(
        private TradeRepository $tradeRepository,
        private EmployeeRoleRepository $employeeRoleRepository,
        private ResourceTypeRepository $resourceTypeRepository,
        private ResourceCategoryRepository $resourceCategoryRepository,
    ) {
    }

    public function getMetadata(): array
    {
        $trades = $this->tradeRepository->findAll();
        $employeeRoles = $this->employeeRoleRepository->findAll();
        $resourceTypes = $this->resourceTypeRepository->findAll();
        $resourceCategories = $this->resourceCategoryRepository->findAll();

        $tradeDTOs = [];
        foreach ($trades as $trade) {
            $tradeDTOs[] = GetTradeResponseDTO::fromEntity($trade);
        }

        $resourceTypeDTOs = [];
        foreach ($resourceTypes as $resourceType) {
            $resourceTypeDTOs[] = GetResourceTypeResponseDTO::fromEntity($resourceType);
        }

        $resourceCategoryDTOs = [];
        foreach ($resourceCategories as $resourceCategory) {
            $resourceCategoryDTOs[] = GetResourceCategoriesResponseDTO::fromEntity($resourceCategory);
        }

        $employeeRoleDTOs = [];
        foreach ($employeeRoles as $resourceType) {
            $employeeRoleDTOs[] = GetEmployeeRolesResponseDTO::fromEntity($resourceType);
        }

        return [
            'filters' => [
                'trades' => $tradeDTOs,
                'employeeRoles' => $employeeRoleDTOs,
                'resourceTypes' => $resourceTypeDTOs,
                'resourceCategories' => $resourceCategoryDTOs,
            ],
        ];
    }
}
