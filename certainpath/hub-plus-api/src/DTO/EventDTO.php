<?php

namespace App\DTO;

class EventDTO
{
    public ?int $id;
    public string $eventCode;
    public int $eventTypeId;
    public ?int $eventInstructorId;
    public string $eventName;
    public string $eventDescription;
    public int $eventCategoryId;
    public float $eventPrice;
    public ?bool $isEligibleForReturningStudent;
    public ?bool $isVoucherEligible;
    public ?bool $isDeleted;
    public ?bool $isPublished;
    public ?string $sgiVoucherValue;
    public bool $hideFromCalendar;
    public bool $hideFromCatalog;
    public \DateTimeInterface $createdAt;
    public \DateTimeInterface $updatedAt;
    public ?bool $certainPath;

    public function __construct(array $data)
    {
        $this->id = $data['id'] ?? null;
        $this->eventCode = $data['event_code'];
        $this->eventTypeId = $data['event_type_id'];
        $this->eventInstructorId = $data['event_instructor_id'] ?? null;
        $this->eventName = $data['event_name'];
        $this->eventDescription = $data['event_description'];
        $this->eventCategoryId = $data['event_category_id'];
        $this->eventPrice = (float) $data['event_price'];
        $this->isEligibleForReturningStudent = $data['is_eligible_for_returning_student'] ?? null;
        $this->isDeleted = $data['is_deleted'] ?? null;
        $this->isPublished = $data['is_published'] ?? null;
        $this->createdAt = new \DateTime($data['created_at']);
        $this->updatedAt = new \DateTime($data['updated_at']);
    }

    public static function createFromEntity(\App\Entity\Event $event): self
    {
        return new self([
            'id' => $event->getId(),
            'event_code' => $event->getEventCode(),
            'event_type_id' => $event->getEventType()->getId(),
            'event_name' => $event->getEventName(),
            'event_description' => $event->getEventDescription(),
            'event_category_id' => $event->getEventCategory()->getId(),
            'event_price' => $event->getEventPrice(),
            'is_deleted' => $event->isDeleted(),
            'is_published' => $event->getIsPublished(),
            'created_at' => $event->getCreatedAt()->format('Y-m-d H:i:s'),
            'updated_at' => $event->getUpdatedAt()->format('Y-m-d H:i:s'),
        ]);
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'event_code' => $this->eventCode,
            'event_type_id' => $this->eventTypeId,
            'event_instructor_id' => $this->eventInstructorId,
            'event_name' => $this->eventName,
            'event_description' => $this->eventDescription,
            'event_category_id' => $this->eventCategoryId,
            'event_price' => $this->eventPrice,
            'is_eligible_for_returning_student' => $this->isEligibleForReturningStudent,
            'is_voucher_eligible' => $this->isVoucherEligible,
            'is_deleted' => $this->isDeleted,
            'is_published' => $this->isPublished,
            'sgi_voucher_value' => $this->sgiVoucherValue,
            'hide_from_calendar' => $this->hideFromCalendar,
            'hide_from_catalog' => $this->hideFromCatalog,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
            'certain_path' => $this->certainPath,
        ];
    }
}
