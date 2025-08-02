<?php

declare(strict_types=1);

namespace App\Service\Resource;

use App\DTO\Response\Resource\GetResourceFilterMetaDataResponseDTO;
use App\Repository\EmployeeRoleRepository;
use App\Repository\ResourceTypeRepository;
use App\Repository\TradeRepository;

readonly class GetResourceFilterMetaDataService
{
    public function __construct(
        private ResourceTypeRepository $resourceTypeRepository,
        private EmployeeRoleRepository $employeeRoleRepository,
        private TradeRepository $tradeRepository,
    ) {
    }

    public function getFilterMetadata(): GetResourceFilterMetaDataResponseDTO
    {
        $resourceTypesWithCounts = $this->resourceTypeRepository->findAllWithResourceCounts();

        $resourceTypeData = array_map(
            static function (array $row) {
                return [
                    'id' => (int) $row['id'],
                    'name' => $row['name'],
                    'resourceCount' => (int) $row['resourceCount'],
                ];
            },
            $resourceTypesWithCounts
        );

        $employeeRoles = $this->employeeRoleRepository->findAll();
        $employeeRoleData = array_map(static function ($role) {
            return [
                'id' => $role->getId(),
                'name' => $role->getName(),
            ];
        }, $employeeRoles);

        $trades = $this->tradeRepository->findAll();
        $tradeData = array_map(static function ($trade) {
            return [
                'id' => $trade->getId(),
                'name' => $trade->getName(),
            ];
        }, $trades);

        return new GetResourceFilterMetaDataResponseDTO(
            resourceTypes: $resourceTypeData,
            employeeRoles: $employeeRoleData,
            trades: $tradeData,
        );
    }
}
