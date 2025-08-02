<?php

declare(strict_types=1);

namespace App\Module\ServiceTitan\Entity;

use App\Entity\Company;
use App\Module\ServiceTitan\Enum\ServiceTitanSyncDataType;
use App\Module\ServiceTitan\Enum\ServiceTitanSyncType;
use App\Module\ServiceTitan\Enum\ServiceTitanSyncStatus;
use App\Entity\Trait\TimestampableDateTimeTZTrait;
use App\Entity\Trait\UuidTrait;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'servicetitan_sync_log')]
#[ORM\HasLifecycleCallbacks]
class ServiceTitanSyncLog
{
    use UuidTrait;
    use TimestampableDateTimeTZTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: ServiceTitanCredential::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ServiceTitanCredential $serviceTitanCredential;

    #[ORM\Column(type: 'string', enumType: ServiceTitanSyncDataType::class)]
    private ServiceTitanSyncDataType $dataType;

    #[ORM\Column(type: 'string', enumType: ServiceTitanSyncType::class, nullable: true)]
    private ?ServiceTitanSyncType $syncType = null;

    #[ORM\Column(type: 'string', enumType: ServiceTitanSyncStatus::class)]
    private ServiceTitanSyncStatus $status;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private \DateTime $startedAt;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTime $completedAt = null;

    #[ORM\Column(type: Types::INTEGER, options: ['default' => 0])]
    private int $recordsProcessed = 0;

    #[ORM\Column(type: Types::INTEGER, options: ['default' => 0])]
    private int $recordsSuccessful = 0;

    #[ORM\Column(type: Types::INTEGER, options: ['default' => 0])]
    private int $recordsFailed = 0;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $errorMessage = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $metadata = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $errorDetails = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $processingTimeSeconds = null;

    public function __construct()
    {
        $this->startedAt = new \DateTime();
        $this->status = ServiceTitanSyncStatus::RUNNING;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getServiceTitanCredential(): ServiceTitanCredential
    {
        return $this->serviceTitanCredential;
    }

    public function setServiceTitanCredential(ServiceTitanCredential $serviceTitanCredential): self
    {
        $this->serviceTitanCredential = $serviceTitanCredential;
        return $this;
    }

    public function getDataType(): ServiceTitanSyncDataType
    {
        return $this->dataType;
    }

    public function setDataType(ServiceTitanSyncDataType $dataType): self
    {
        $this->dataType = $dataType;
        return $this;
    }

    public function getSyncType(): ?ServiceTitanSyncType
    {
        return $this->syncType;
    }

    public function setSyncType(?ServiceTitanSyncType $syncType): self
    {
        $this->syncType = $syncType;
        return $this;
    }

    public function getStatus(): ServiceTitanSyncStatus
    {
        return $this->status;
    }

    public function setStatus(ServiceTitanSyncStatus $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function getStartedAt(): \DateTime
    {
        return $this->startedAt;
    }

    public function setStartedAt(\DateTime $startedAt): self
    {
        $this->startedAt = $startedAt;
        return $this;
    }

    public function getCompletedAt(): ?\DateTime
    {
        return $this->completedAt;
    }

    public function setCompletedAt(?\DateTime $completedAt): self
    {
        $this->completedAt = $completedAt;
        return $this;
    }

    public function getRecordsProcessed(): int
    {
        return $this->recordsProcessed;
    }

    public function setRecordsProcessed(int $recordsProcessed): self
    {
        $this->recordsProcessed = $recordsProcessed;
        return $this;
    }

    public function getRecordsSuccessful(): int
    {
        return $this->recordsSuccessful;
    }

    public function setRecordsSuccessful(int $recordsSuccessful): self
    {
        $this->recordsSuccessful = $recordsSuccessful;
        return $this;
    }

    public function getRecordsFailed(): int
    {
        return $this->recordsFailed;
    }

    public function setRecordsFailed(int $recordsFailed): self
    {
        $this->recordsFailed = $recordsFailed;
        return $this;
    }

    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    public function setErrorMessage(?string $errorMessage): self
    {
        $this->errorMessage = $errorMessage;
        return $this;
    }

    public function getMetadata(): ?array
    {
        return $this->metadata;
    }

    public function setMetadata(?array $metadata): self
    {
        $this->metadata = $metadata;
        return $this;
    }

    public function getErrorDetails(): ?array
    {
        return $this->errorDetails;
    }

    public function setErrorDetails(?array $errorDetails): self
    {
        $this->errorDetails = $errorDetails;
        return $this;
    }

    public function setProcessingTimeSeconds(?int $processingTimeSeconds): self
    {
        $this->processingTimeSeconds = $processingTimeSeconds;
        return $this;
    }

    public function markAsCompleted(): self
    {
        $this->status = ServiceTitanSyncStatus::COMPLETED;
        $this->completedAt = new \DateTime();
        return $this;
    }

    public function markAsFailed(?string $errorMessage = null, ?array $errorDetails = null): self
    {
        $this->status = ServiceTitanSyncStatus::FAILED;
        $this->completedAt = new \DateTime();
        if ($errorMessage) {
            $this->errorMessage = $errorMessage;
        }
        if ($errorDetails) {
            $this->errorDetails = $errorDetails;
        }
        return $this;
    }

    public function isRunning(): bool
    {
        return $this->status === ServiceTitanSyncStatus::RUNNING;
    }

    public function isCompleted(): bool
    {
        return $this->status === ServiceTitanSyncStatus::COMPLETED;
    }

    public function hasFailed(): bool
    {
        return $this->status === ServiceTitanSyncStatus::FAILED;
    }

    public function getSuccessRate(): float
    {
        if ($this->recordsProcessed === 0) {
            return 0.0;
        }

        return ($this->recordsSuccessful / $this->recordsProcessed) * 100;
    }

    public function wasSuccessful(): bool
    {
        return $this->status === ServiceTitanSyncStatus::COMPLETED && $this->recordsFailed === 0;
    }

    public function getProcessingTimeSeconds(): ?int
    {
        // Return stored value if available
        if ($this->processingTimeSeconds !== null) {
            return $this->processingTimeSeconds;
        }

        // Calculate from timestamps if completed
        if ($this->completedAt === null) {
            return null;
        }

        return $this->completedAt->getTimestamp() - $this->startedAt->getTimestamp();
    }

    public function updateRecordCounts(int $processed, int $successful, int $failed): self
    {
        $this->recordsProcessed += $processed;
        $this->recordsSuccessful += $successful;
        $this->recordsFailed += $failed;
        return $this;
    }

    public function markAsRunning(): self
    {
        $this->status = ServiceTitanSyncStatus::RUNNING;
        return $this;
    }

    public function getDurationString(): ?string
    {
        $processingTime = $this->getProcessingTimeSeconds();
        if ($processingTime === null) {
            return null;
        }

        $hours = intval($processingTime / 3600);
        $minutes = intval(($processingTime % 3600) / 60);
        $seconds = $processingTime % 60;

        if ($hours > 0) {
            return sprintf('%dh %dm %ds', $hours, $minutes, $seconds);
        }

        if ($minutes > 0) {
            return sprintf('%dm %ds', $minutes, $seconds);
        }

        return sprintf('%ds', $seconds);
    }
}
