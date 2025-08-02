<?php

declare(strict_types=1);

namespace App\Service\Event;

use App\DTO\Response\Event\GetEventResponseDTO;
use App\Entity\Event;
use App\Entity\EventEmployeeRoleMapping;
use App\Entity\EventFile;
use App\Entity\EventTagMapping;
use App\Entity\EventTradeMapping;

final readonly class GetEventService
{
    public function getEvent(Event $event): GetEventResponseDTO
    {
        $tags = $this->extractTags($event);
        $trades = $this->extractTrades($event);
        $roles = $this->extractRoles($event);
        $files = $this->extractFiles($event);
        $eventTypeId = $event->getEventType()?->getId() ?? null;
        $eventCategoryId = $event->getEventCategory()?->getId() ?? null;

        return new GetEventResponseDTO(
            id: $event->getId(),
            uuid: $event->getUuid(),
            eventCode: $event->getEventCode(),
            eventName: $event->getEventName(),
            eventDescription: $event->getEventDescription(),
            eventPrice: $event->getEventPrice(),
            isPublished: (bool) $event->getIsPublished(),
            isVoucherEligible: (bool) $event->isVoucherEligible(),
            eventTypeId: $eventTypeId,
            eventTypeName: $event->getEventTypeName(),
            eventCategoryId: $eventCategoryId,
            eventCategoryName: $event->getEventCategoryName(),
            thumbnailUrl: $event->getThumbnail()?->getUrl(),
            thumbnailFileId: $event->getThumbnail()?->getId(),
            thumbnailFileUuid: $event->getThumbnail()?->getUuid(),
            tags: $tags,
            trades: $trades,
            roles: $roles,
            files: $files
        );
    }

    /**
     * @return array<int, array{id:int,name:string}>
     */
    private function extractTags(Event $event): array
    {
        $mapped = $event->getEventTagMappings()->map(
            function (EventTagMapping $m) {
                $tag = $m->getEventTag();
                if (!$tag) {
                    return null;
                }

                return [
                    'id' => $tag->getId(),
                    'name' => $tag->getName(),
                ];
            }
        )->filter(fn ($item) => null !== $item);

        return $mapped->toArray();
    }

    /**
     * @return array<int, array{id:int,name:string}>
     */
    private function extractTrades(Event $event): array
    {
        $mapped = $event->getEventTradeMappings()->map(
            function (EventTradeMapping $m) {
                $trade = $m->getTrade();
                if (!$trade) {
                    return null;
                }

                return [
                    'id' => $trade->getId(),
                    'name' => $trade->getName(),
                ];
            }
        )->filter(fn ($item) => null !== $item);

        return $mapped->toArray();
    }

    /**
     * @return array<int, array{id:int,name:string}>
     */
    private function extractRoles(Event $event): array
    {
        $mapped = $event->getEventEmployeeRoleMappings()->map(
            function (EventEmployeeRoleMapping $m) {
                $role = $m->getEmployeeRole();
                if (!$role) {
                    return null;
                }

                return [
                    'id' => $role->getId(),
                    'name' => $role->getName(),
                ];
            }
        )->filter(fn ($item) => null !== $item);

        return $mapped->toArray();
    }

    /**
     * @return array<int, array{
     *   id: int,
     *   uuid: string,
     *   originalFileName: string|null,
     *   fileUrl: string|null,
     * }>
     */
    private function extractFiles(Event $event): array
    {
        return $event->getEventFiles()->map(
            function (EventFile $eventFile) {
                return [
                    'id' => $eventFile->getFile()->getId(),
                    'uuid' => $eventFile->getFile()->getUuid(),
                    'originalFileName' => $eventFile->getFile()->getOriginalFileName(),
                    'fileUrl' => $eventFile->getFile()->getUrl(),
                ];
            }
        )->toArray();
    }
}
