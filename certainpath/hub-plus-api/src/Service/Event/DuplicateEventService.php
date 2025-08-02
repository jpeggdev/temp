<?php

declare(strict_types=1);

namespace App\Service\Event;

use App\Constants\S3Buckets;
use App\DTO\Response\Event\DuplicateEventResponseDTO;
use App\Entity\Event;
use App\Entity\EventEmployeeRoleMapping;
use App\Entity\EventFile;
use App\Entity\EventTagMapping;
use App\Entity\EventTradeMapping;
use App\Entity\File;
use App\Exception\Event\EventCreateUpdateException;
use App\Repository\EventRepository\EventRepository;
use App\Service\AmazonS3Service;
use Doctrine\ORM\EntityManagerInterface;

readonly class DuplicateEventService
{
    public function __construct(
        private EventRepository $eventRepository,
        private EntityManagerInterface $em,
        private AmazonS3Service $amazonS3Service,
    ) {
    }

    public function duplicateEvent(Event $sourceEvent): DuplicateEventResponseDTO
    {
        $this->em->beginTransaction();

        try {
            $duplicated = $this->createBaseEvent($sourceEvent);
            $newCode = $this->generateUniqueEventCode();
            $duplicated->setEventCode($newCode);
            $this->copyEventType($sourceEvent, $duplicated);
            $this->copyCategory($sourceEvent, $duplicated);
            $this->copyThumbnail($sourceEvent, $duplicated);
            $this->copyEventFiles($sourceEvent, $duplicated);
            $this->copyTagMappings($sourceEvent, $duplicated);
            $this->copyTradeMappings($sourceEvent, $duplicated);
            $this->copyRoleMappings($sourceEvent, $duplicated);
            $this->eventRepository->save($duplicated, true);
            $this->em->commit();

            return new DuplicateEventResponseDTO(
                id: $duplicated->getId(),
                uuid: $duplicated->getUuid(),
                eventCode: $duplicated->getEventCode(),
                eventName: $duplicated->getEventName(),
                thumbnailUrl: $duplicated->getThumbnail()?->getUrl(),
                isPublished: (bool) $duplicated->getIsPublished(),
                isVoucherEligible: (bool) $duplicated->isVoucherEligible(),
            );
        } catch (\Throwable $e) {
            $this->em->rollback();
            throw $e;
        } finally {
            $this->em->clear();
        }
    }

    private function createBaseEvent(Event $source): Event
    {
        $newEvent = new Event();
        $newEvent->setEventName($source->getEventName());
        $newEvent->setEventDescription($source->getEventDescription());
        $newEvent->setEventPrice($source->getEventPrice());
        $newEvent->setIsPublished(false);
        $newEvent->setIsVoucherEligible($source->isVoucherEligible());

        return $newEvent;
    }

    private function generateUniqueEventCode(): string
    {
        $suffixLength = 6;
        do {
            $randomHex = substr(bin2hex(random_bytes(8)), 0, $suffixLength);
            $found = $this->eventRepository->findOneBy(['eventCode' => $randomHex]);
        } while ($found);

        return $randomHex;
    }

    private function copyEventType(Event $source, Event $target): void
    {
        if ($source->getEventType()) {
            $target->setEventType($source->getEventType());
        }
    }

    private function copyCategory(Event $source, Event $target): void
    {
        if ($source->getEventCategory()) {
            $target->setEventCategory($source->getEventCategory());
        }
    }

    private function copyThumbnail(Event $source, Event $target): void
    {
        $thumb = $source->getThumbnail();
        if (!$thumb) {
            return;
        }
        $newFile = $this->duplicateSingleFile($thumb, S3Buckets::CERTAIN_PATH_PUBLIC_BUCKET);
        $target->setThumbnail($newFile);
    }

    private function copyEventFiles(Event $source, Event $target): void
    {
        foreach ($source->getEventFiles() as $oldAssoc) {
            $oldFile = $oldAssoc->getFile();
            if (!$oldFile) {
                continue;
            }
            $newFile = $this->duplicateSingleFile($oldFile, S3Buckets::MEMBERSHIP_FILES_BUCKET);
            $dupFileAssoc = new EventFile();
            $dupFileAssoc->setFile($newFile)->setEvent($target);
            $target->addEventFile($dupFileAssoc);
            $this->em->persist($dupFileAssoc);
        }
    }

    private function duplicateSingleFile(File $oldFile, string $targetBucket): File
    {
        $oldBucket = $oldFile->getBucketName();
        $oldKey = $oldFile->getObjectKey();
        $contentType = $oldFile->getContentType() ?? 'application/octet-stream';
        $extension = pathinfo($oldKey, PATHINFO_EXTENSION);
        $uniqueKey = sprintf(
            '%s/%s_%s.%s',
            $targetBucket,
            uniqid('file_', true),
            md5((string) microtime(true)),
            $extension ?: 'bin'
        );
        $presignedUrl = $this->amazonS3Service->generatePresignedUrl($oldBucket, $oldKey, 300);
        $fileContent = @file_get_contents($presignedUrl);
        if (false === $fileContent) {
            throw new EventCreateUpdateException(sprintf('Failed to read source file from S3: bucket=%s, key=%s', $oldBucket, $oldKey));
        }
        $newUrl = $this->amazonS3Service->uploadFile(
            $targetBucket,
            $fileContent,
            $uniqueKey,
            $contentType
        );
        $newFile = new File();
        $newFile->setOriginalFilename($oldFile->getOriginalFilename());
        $newFile->setBucketName($targetBucket);
        $newFile->setObjectKey($uniqueKey);
        $newFile->setContentType($contentType);
        $newFile->setMimeType($oldFile->getMimeType());
        $newFile->setFileSize($oldFile->getFileSize());
        $newFile->setUrl($newUrl);
        $this->em->persist($newFile);

        return $newFile;
    }

    private function copyTagMappings(Event $source, Event $target): void
    {
        foreach ($source->getEventTagMappings() as $oldMap) {
            if (!$oldMap->getEventTag()) {
                continue;
            }
            $dupMap = new EventTagMapping();
            $dupMap->setEventTag($oldMap->getEventTag());
            $dupMap->setEvent($target);
            $target->addEventTagMapping($dupMap);
            $this->em->persist($dupMap);
        }
    }

    private function copyTradeMappings(Event $source, Event $target): void
    {
        foreach ($source->getEventTradeMappings() as $oldMap) {
            if (!$oldMap->getTrade()) {
                continue;
            }
            $dupMap = new EventTradeMapping();
            $dupMap->setTrade($oldMap->getTrade());
            $dupMap->setEvent($target);
            $target->addEventTradeMapping($dupMap);
            $this->em->persist($dupMap);
        }
    }

    private function copyRoleMappings(Event $source, Event $target): void
    {
        foreach ($source->getEventEmployeeRoleMappings() as $oldMap) {
            if (!$oldMap->getEmployeeRole()) {
                continue;
            }
            $dupMap = new EventEmployeeRoleMapping();
            $dupMap->setEmployeeRole($oldMap->getEmployeeRole());
            $dupMap->setEvent($target);
            $target->addEventEmployeeRoleMapping($dupMap);
            $this->em->persist($dupMap);
        }
    }
}
