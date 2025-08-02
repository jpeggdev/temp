<?php

namespace App\Entity;

use App\Repository\EventStatusRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EventStatusRepository::class)]
#[ORM\UniqueConstraint(name: "event_status_name_uniq", columns: ["name"])]
class EventStatus
{
    public const PENDING = 'pending';
    public const PROCESSING = 'processing';
    public const CREATED = 'created';
    public const ACTIVE = 'active';
    public const FAILED = 'failed';
    public const COMPLETED = 'completed';
    public const PAUSED = 'paused';
    public const RESUMING = 'resuming';

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $name = null;

    public function __construct(string $statusName)
    {
        $this->name = $statusName;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public static function pending(): EventStatus
    {
        return new self(self::PENDING);
    }

    public static function processing(): EventStatus
    {
        return new self(self::PROCESSING);
    }

    public static function created(): EventStatus
    {
        return new self(self::CREATED);
    }

    public static function active(): EventStatus
    {
        return new self(self::ACTIVE);
    }

    public static function failed(): EventStatus
    {
        return new self(self::FAILED);
    }

    public static function completed(): EventStatus
    {
        return new self(self::COMPLETED);
    }

    public static function paused(): EventStatus
    {
        return new self(self::PAUSED);
    }

    public static function resuming(): EventStatus
    {
        return new self(self::RESUMING);
    }

    public function equals(EventStatus $compare): bool
    {
        return $this->name === $compare->name;
    }

    public function isPending(): bool
    {
        return $this->equals(self::pending());
    }

    public function isProcessing(): bool
    {
        return $this->equals(self::processing());
    }

    public function isCreated(): bool
    {
        return $this->equals(self::created());
    }

    public function isActive(): bool
    {
        return $this->equals(self::active());
    }

    public function isFailed(): bool
    {
        return $this->equals(self::failed());
    }

    public function isCompleted(): bool
    {
        return $this->equals(self::completed());
    }

    public function isPaused(): bool
    {
        return $this->equals(self::paused());
    }

    public function isResuming(): bool
    {
        return $this->equals(self::resuming());
    }
}
