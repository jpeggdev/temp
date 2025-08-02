<?php

declare(strict_types=1);

namespace App\Service\Event;

use App\DTO\Request\Event\GetEventSearchResultsQueryDTO;
use App\DTO\Response\Event\GetEventSearchResultsResponseDTO;
use App\Entity\Employee;
use App\Entity\Event;
use App\Entity\File;
use App\Repository\EmployeeRoleRepository;
use App\Repository\EventCategoryRepository;
use App\Repository\EventRepository\EventRepository;
use App\Repository\EventTypeRepository;
use App\Repository\TradeRepository;
use App\Service\AmazonS3Service;

final readonly class GetEventSearchResultsService
{
    public function __construct(
        private EventRepository $eventRepository,
        private EventTypeRepository $eventTypeRepository,
        private EventCategoryRepository $eventCategoryRepository,
        private TradeRepository $tradeRepository,
        private EmployeeRoleRepository $employeeRoleRepository,
        private AmazonS3Service $amazonS3Service,
    ) {
    }

    /**
     * Return event search results + facets.
     * Now includes trade & employeeRole facets in the response.
     *
     * @return array{
     *     data: array{
     *         events: GetEventSearchResultsResponseDTO[],
     *         filters: array{
     *             eventTypes: array<array{id:int,name:string,eventCount:int}>,
     *             categories: array<array{id:int,name:string,eventCount:int}>,
     *             trades: array<array{id:int,name:string,eventCount:int}>,
     *             employeeRoles: array<array{id:int,name:string,eventCount:int}>
     *         }
     *     },
     *     totalCount: int
     * }
     */
    public function getEvents(GetEventSearchResultsQueryDTO $queryDto, ?Employee $employee = null): array
    {
        $events = $this->eventRepository->findPublishedEventsByQuery($queryDto, $employee);

        $totalCount = $this->eventRepository->getPublishedTotalCount($queryDto, $employee);

        $eventDtos = array_map(
            fn (Event $event) => GetEventSearchResultsResponseDTO::fromEntity(
                $event,
                $this->getPresignedUrlForFile($event->getThumbnail())
            ),
            $events
        );

        $typeFacetRows = $this->eventTypeRepository->findAllWithFilteredEventCounts($queryDto, $employee);
        $eventTypesFacet = array_map(
            static fn (array $row) => [
                'id' => (int) $row['id'],
                'name' => $row['name'],
                'eventCount' => (int) $row['eventCount'],
            ],
            $typeFacetRows
        );

        $categoryFacetRows = $this->eventCategoryRepository->findAllWithFilteredEventCounts($queryDto, $employee);
        $categoriesFacet = array_map(
            static fn (array $row) => [
                'id' => (int) $row['id'],
                'name' => $row['name'],
                'eventCount' => (int) $row['eventCount'],
            ],
            $categoryFacetRows
        );

        $roleFacetRows = $this->employeeRoleRepository->findAllWithFilteredEventCounts($queryDto, $employee);
        $employeeRolesFacet = array_map(
            static fn (array $row) => [
                'id' => (int) $row['id'],
                'name' => $row['name'],
                'eventCount' => (int) $row['eventCount'],
            ],
            $roleFacetRows
        );

        $tradeFacetRows = $this->tradeRepository->findAllWithFilteredEventCounts($queryDto, $employee);
        $tradesFacet = array_map(
            static fn (array $row) => [
                'id' => (int) $row['id'],
                'name' => $row['name'],
                'eventCount' => (int) $row['eventCount'],
            ],
            $tradeFacetRows
        );

        return [
            'data' => [
                'events' => $eventDtos,
                'filters' => [
                    'eventTypes' => $eventTypesFacet,
                    'categories' => $categoriesFacet,
                    'trades' => $tradesFacet,
                    'employeeRoles' => $employeeRolesFacet,
                ],
            ],
            'totalCount' => $totalCount,
        ];
    }

    private function getPresignedUrlForFile(?File $file): ?string
    {
        if (!$file || !$file->getBucketName() || !$file->getObjectKey()) {
            return null;
        }

        try {
            return $this->amazonS3Service->generatePresignedUrl(
                $file->getBucketName(),
                $file->getObjectKey()
            );
        } catch (\Exception $e) {
            return null;
        }
    }
}
