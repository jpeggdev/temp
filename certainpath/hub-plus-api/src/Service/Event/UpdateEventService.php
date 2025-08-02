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

readonly class UpdateEventService
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

    public function updateEvent(
        Event $event,
        CreateUpdateEventDTO $dto,
    ): CreateUpdateEventResponseDTO {
        $this->em->beginTransaction();

        try {
            $this->validateEventCode($event, $dto->eventCode);
            $this->updateBaseEventFields($event, $dto);
            $this->updateEventCategory($event, $dto->eventCategoryId);
            $this->updateEventType($event, $dto->eventTypeId);
            $this->updateThumbnail($event, $dto);

            // Prioritize UUID-based file references if available
            if (!empty($dto->fileUuids)) {
                $this->updateEventFilesByUuid($event, $dto->fileUuids);
            } else {
                $this->updateEventFiles($event, $dto->fileIds);
            }

            $this->updateTagMappings($event, $dto->tagIds);
            $this->updateTradeMappings($event, $dto->tradeIds);
            $this->updateRoleMappings($event, $dto->roleIds);

            $this->eventRepository->save($event, true);
            $this->em->commit();

            return new CreateUpdateEventResponseDTO(
                id: $event->getId(),
                uuid: $event->getUuid(),
                eventCode: $event->getEventCode(),
                eventName: $event->getEventName(),
                thumbnailUrl: $event->getThumbnail()?->getUrl() ?? $dto->thumbnailUrl,
                isPublished: (bool) $event->getIsPublished(),
                isVoucherEligible: (bool) $event->isVoucherEligible(),
            );
        } catch (\Throwable $e) {
            $this->em->rollback();
            throw $e;
        } finally {
            $this->em->clear();
        }
    }

    private function validateEventCode(Event $existingEvent, string $newCode): void
    {
        if ($existingEvent->getEventCode() === $newCode) {
            return;
        }

        $found = $this->eventRepository->findOneBy(['eventCode' => $newCode]);
        if ($found) {
            throw new EventCreateUpdateException(sprintf('An event with code "%s" already exists.', $newCode));
        }
    }

    private function updateBaseEventFields(Event $event, CreateUpdateEventDTO $dto): void
    {
        $event->setEventCode($dto->eventCode);
        $event->setEventName($dto->eventName);
        $event->setEventDescription($dto->eventDescription);
        $event->setEventPrice($dto->eventPrice);
        $event->setIsPublished($dto->isPublished);
        $event->setIsVoucherEligible($dto->isVoucherEligible);
    }

    private function updateEventCategory(Event $event, ?int $categoryId): void
    {
        if (null === $categoryId) {
            $event->setEventCategory(null);

            return;
        }

        $cat = $this->eventCategoryRepository->find($categoryId);
        if (!$cat) {
            throw new EventCreateUpdateException(sprintf('EventCategory with ID %d not found.', $categoryId));
        }
        $event->setEventCategory($cat);
    }

    private function updateEventType(Event $event, ?int $typeId): void
    {
        if (null === $typeId) {
            $event->setEventType(null);

            return;
        }

        $eventType = $this->eventTypeRepository->find($typeId);
        if (!$eventType) {
            throw new EventCreateUpdateException(sprintf('EventType with ID %d not found.', $typeId));
        }
        $event->setEventType($eventType);
    }

    private function updateThumbnail(Event $event, CreateUpdateEventDTO $dto): void
    {
        // If we have a UUID, use that (prioritize UUID over ID)
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

        // If UUID is null but ID is null too, clear the thumbnail
        if (null === $dto->thumbnailFileId) {
            $event->setThumbnail(null);
            return;
        }

        // Fallback to ID-based lookup for backward compatibility
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

    /**
     * @param int[] $fileIds
     */
    private function updateEventFiles(Event $event, array $fileIds): void
    {
        foreach ($event->getEventFiles()->toArray() as $oldFile) {
            $this->em->remove($oldFile);
        }
        $event->getEventFiles()->clear();

        foreach ($fileIds as $fid) {
            $file = $this->fileRepository->find($fid);
            if (!$file) {
                throw new EventCreateUpdateException(sprintf('File with ID %d not found (event file).', $fid));
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

    /**
     * @param string[] $fileUuids
     */
    private function updateEventFilesByUuid(Event $event, array $fileUuids): void
    {
        foreach ($event->getEventFiles()->toArray() as $oldFile) {
            $this->em->remove($oldFile);
        }
        $event->getEventFiles()->clear();

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

    /**
     * @param int[] $tagIds
     */
    private function updateTagMappings(Event $event, array $tagIds): void
    {
        foreach ($event->getEventTagMappings()->toArray() as $oldMapping) {
            $this->em->remove($oldMapping);
        }
        $event->getEventTagMappings()->clear();

        foreach ($tagIds as $tid) {
            if ($tag = $this->eventTagRepository->find($tid)) {
                $map = new EventTagMapping();
                $map->setEvent($event)->setEventTag($tag);
                $event->addEventTagMapping($map);
                $this->em->persist($map);
            }
        }
    }

    /**
     * @param int[] $tradeIds
     */
    private function updateTradeMappings(Event $event, array $tradeIds): void
    {
        foreach ($event->getEventTradeMappings()->toArray() as $oldMapping) {
            $this->em->remove($oldMapping);
        }
        $event->getEventTradeMappings()->clear();

        foreach ($tradeIds as $tid) {
            if ($trade = $this->tradeRepository->find($tid)) {
                $map = new EventTradeMapping();
                $map->setEvent($event)->setTrade($trade);
                $event->addEventTradeMapping($map);
                $this->em->persist($map);
            }
        }
    }

    /**
     * @param int[] $roleIds
     */
    private function updateRoleMappings(Event $event, array $roleIds): void
    {
        foreach ($event->getEventEmployeeRoleMappings()->toArray() as $oldMapping) {
            $this->em->remove($oldMapping);
        }
        $event->getEventEmployeeRoleMappings()->clear();

        foreach ($roleIds as $rid) {
            if ($role = $this->employeeRoleRepository->find($rid)) {
                $map = new EventEmployeeRoleMapping();
                $map->setEvent($event)->setEmployeeRole($role);
                $event->addEventEmployeeRoleMapping($map);
                $this->em->persist($map);
            }
        }
    }
}
