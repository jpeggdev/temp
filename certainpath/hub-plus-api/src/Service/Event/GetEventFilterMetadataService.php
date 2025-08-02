<?php

declare(strict_types=1);

namespace App\Service\Event;

use App\DTO\Response\Event\GetEventFilterMetadataResponseDTO;
use App\Repository\EmployeeRoleRepository;
use App\Repository\EventCategoryRepository;
use App\Repository\EventTagRepository;
use App\Repository\EventTypeRepository;
use App\Repository\TradeRepository;

readonly class GetEventFilterMetadataService
{
    public function __construct(
        private EventTypeRepository $eventTypeRepository,
        private EventCategoryRepository $eventCategoryRepository,
        private EmployeeRoleRepository $employeeRoleRepository,
        private TradeRepository $tradeRepository,
        private EventTagRepository $eventTagRepository,
    ) {
    }

    public function getFilterMetadata(): GetEventFilterMetadataResponseDTO
    {
        $eventTypes = $this->eventTypeRepository->findAll();
        $eventTypeData = array_map(static function ($type) {
            return [
                'id' => $type->getId(),
                'name' => $type->getName(),
            ];
        }, $eventTypes);

        $eventCategories = $this->eventCategoryRepository->findAll();
        $eventCategoryData = array_map(static function ($cat) {
            return [
                'id' => $cat->getId(),
                'name' => $cat->getName(),
            ];
        }, $eventCategories);

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

        $eventTags = $this->eventTagRepository->findAll();
        $eventTagData = array_map(static function ($tag) {
            return [
                'id' => $tag->getId(),
                'name' => $tag->getName(),
            ];
        }, $eventTags);

        return new GetEventFilterMetadataResponseDTO(
            eventTypes: $eventTypeData,
            eventCategories: $eventCategoryData,
            employeeRoles: $employeeRoleData,
            trades: $tradeData,
            eventTags: $eventTagData,
        );
    }
}
