<?php

declare(strict_types=1);

namespace App\Service\Event;

use App\DTO\Request\Event\CreateUpdateEventDTO;
use App\DTO\Response\Event\CreateUpdateEventResponseDTO;
use App\Entity\Event;
use App\Entity\EventEmployeeRoleMapping;
use App\Entity\EventFile;
use App\Entity\EventTagMapping;
use App\Entity\EventTradeMapping;
use App\Exception\Event\EventCreateUpdateException;
use App\Repository\EmployeeRoleRepository;
use App\Repository\EventCategoryRepository;
use App\Repository\EventRepository\EventRepository;
use App\Repository\EventTagRepository;
use App\Repository\EventTypeRepository;
use App\Repository\FileRepository;
use App\Repository\TradeRepository;
use Doctrine\ORM\EntityManagerInterface;

readonly class CreateEventService
{
    public function __construct(
        private EventRepository $eventRepository,
        private EventTagRepository $eventTagRepository,
        private TradeRepository $tradeRepository,
        private EmployeeRoleRepository $employeeRoleRepository,
        private EventCategoryRepository $eventCategoryRepository,
        private EventTypeRepository $eventTypeRepository,
        private FileRepository $fileRepository,
        private EntityManagerInterface $em,
    ) {
    }

    public function createEvent(CreateUpdateEventDTO $dto): CreateUpdateEventResponseDTO
    {
        $this->em->beginTransaction();

        try {
            $this->ensureUniqueEventCode($dto->eventCode);
            $event = $this->createBaseEvent($dto);
            $this->attachCategoryIfPresent($dto, $event);
            $this->attachTypeIfPresent($dto, $event);
            $this->attachThumbnailIfPresent($dto, $event);

            // Prioritize UUID-based file references if available
            if (!empty($dto->fileUuids)) {
                $this->attachEventFilesByUuid($dto->fileUuids, $event);
            } else {
                $this->attachEventFiles($dto->fileIds, $event);
            }

            $this->attachTagMappings($dto->tagIds, $event);
            $this->attachTradeMappings($dto->tradeIds, $event);
            $this->attachRoleMappings($dto->roleIds, $event);

            $this->eventRepository->save($event, true);
            $this->em->commit();

            return new CreateUpdateEventResponseDTO(
                id: $event->getId(),
                uuid: $event->getUuid(),
                eventCode: $event->getEventCode(),
                eventName: $event->getEventName(),
                thumbnailUrl: $event->getThumbnail()?->getUrl() ?? $dto->thumbnailUrl,
                isPublished: (bool) $event->getIsPublished(),
                isVoucherEligible: (bool) $event->IsVoucherEligible(),
            );
        } catch (\Throwable $e) {
            $this->em->rollback();
            throw $e;
        } finally {
            $this->em->clear();
        }
    }

    private function createBaseEvent(CreateUpdateEventDTO $dto): Event
    {
        $event = new Event();
        $event->setEventCode($dto->eventCode);
        $event->setEventName($dto->eventName);
        $event->setEventDescription($dto->eventDescription);
        $event->setEventPrice($dto->eventPrice);
        $event->setIsPublished($dto->isPublished);
        $event->setIsVoucherEligible($dto->isVoucherEligible);

        return $event;
    }

    private function ensureUniqueEventCode(string $eventCode): void
    {
        if ($this->eventRepository->findOneBy(['eventCode' => $eventCode])) {
            throw new EventCreateUpdateException(sprintf('An event with code "%s" already exists.', $eventCode));
        }
    }

    private function attachCategoryIfPresent(CreateUpdateEventDTO $dto, Event $event): void
    {
        if (null === $dto->eventCategoryId) {
            return;
        }

        $category = $this->eventCategoryRepository->find($dto->eventCategoryId);
        if (!$category) {
            throw new EventCreateUpdateException(sprintf('EventCategory with ID %d not found.', $dto->eventCategoryId));
        }
        $event->setEventCategory($category);
    }

    private function attachTypeIfPresent(CreateUpdateEventDTO $dto, Event $event): void
    {
        if (null === $dto->eventTypeId) {
            return;
        }

        $eventType = $this->eventTypeRepository->find($dto->eventTypeId);
        if (!$eventType) {
            throw new EventCreateUpdateException(sprintf('EventType with ID %d not found.', $dto->eventTypeId));
        }
        $event->setEventType($eventType);
    }

    private function attachThumbnailIfPresent(CreateUpdateEventDTO $dto, Event $event): void
    {
        // Prioritize UUID if present
        if (null !== $dto->thumbnailFileUuid) {
            $file = $this->fileRepository->findOneByUuid($dto->thumbnailFileUuid);
            if (!$file) {
                throw new EventCreateUpdateException(sprintf('File with UUID %s not found (thumbnail).', $dto->thumbnailFileUuid));
            }

            if ($tmp = $file->getFileTmp()) {
                $tmp->setIsCommited(true);
                $this->em->persist($tmp);
            }
            $event->setThumbnail($file);
            return;
        }

        // Fallback to ID-based lookup for backward compatibility
        if (null === $dto->thumbnailFileId) {
            return;
        }

        $file = $this->fileRepository->find($dto->thumbnailFileId);
        if (!$file) {
            throw new EventCreateUpdateException(sprintf('File with ID %d not found (thumbnail).', $dto->thumbnailFileId));
        }

        if ($tmp = $file->getFileTmp()) {
            $tmp->setIsCommited(true);
            $this->em->persist($tmp);
        }
        $event->setThumbnail($file);
    }

    /** @param int[] $fileIds */
    private function attachEventFiles(array $fileIds, Event $event): void
    {
        foreach ($fileIds as $fileId) {
            $file = $this->fileRepository->find($fileId);
            if (!$file) {
                throw new EventCreateUpdateException(sprintf('File with ID %d not found (event file).', $fileId));
            }

            if ($tmp = $file->getFileTmp()) {
                $tmp->setIsCommited(true);
                $this->em->persist($tmp);
            }

            $eventFile = new EventFile();
            $eventFile->setFile($file)->setEvent($event);
            $event->addEventFile($eventFile);
            $this->em->persist($eventFile);
        }
    }

    /** @param string[] $fileUuids */
    private function attachEventFilesByUuid(array $fileUuids, Event $event): void
    {
        foreach ($fileUuids as $uuid) {
            $file = $this->fileRepository->findOneByUuid($uuid);
            if (!$file) {
                throw new EventCreateUpdateException(sprintf('File with UUID %s not found (event file).', $uuid));
            }

            if ($tmp = $file->getFileTmp()) {
                $tmp->setIsCommited(true);
                $this->em->persist($tmp);
            }

            $eventFile = new EventFile();
            $eventFile->setFile($file)->setEvent($event);
            $event->addEventFile($eventFile);
            $this->em->persist($eventFile);
        }
    }

    /** @param int[] $tagIds */
    private function attachTagMappings(array $tagIds, Event $event): void
    {
        foreach ($tagIds as $id) {
            $tag = $this->eventTagRepository->find($id);
            if ($tag) {
                $map = new EventTagMapping();
                $map->setEvent($event)->setEventTag($tag);
                $event->addEventTagMapping($map);
                $this->em->persist($map);
            }
        }
    }

    /** @param int[] $tradeIds */
    private function attachTradeMappings(array $tradeIds, Event $event): void
    {
        foreach ($tradeIds as $id) {
            $trade = $this->tradeRepository->find($id);
            if ($trade) {
                $map = new EventTradeMapping();
                $map->setEvent($event)->setTrade($trade);
                $event->addEventTradeMapping($map);
                $this->em->persist($map);
            }
        }
    }

    /** @param int[] $roleIds */
    private function attachRoleMappings(array $roleIds, Event $event): void
    {
        foreach ($roleIds as $id) {
            $role = $this->employeeRoleRepository->find($id);
            if ($role) {
                $map = new EventEmployeeRoleMapping();
                $map->setEvent($event)->setEmployeeRole($role);
                $event->addEventEmployeeRoleMapping($map);
                $this->em->persist($map);
            }
        }
    }
}
