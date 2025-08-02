<?php

declare(strict_types=1);

namespace App\Entity;

use App\DTO\LogEventDTO;
use App\Enum\AuditLogOperation;
use App\Repository\AuditLogRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AuditLogRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'audit_log')]
class AuditLog
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    private ?string $organization;

    #[ORM\ManyToOne(inversedBy: 'auditLogs')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?User $author;

    #[ORM\Column]
    private string $entityIdentifier;

    #[ORM\Column]
    private AuditLogOperation $operationType;

    #[ORM\Column]
    private string $entityNamespace;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?string $requestData;

    #[ORM\Column(type: 'json')]
    private string $eventData;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    public static function fromLogEventDTO(LogEventDTO $logEventDTO): AuditLog
    {
        $auditEvent = (new self());

        if ($logEventDTO->author instanceof User || null === $logEventDTO->author) {
            $auditEvent->author = $logEventDTO->author;
        } else {
            throw new \InvalidArgumentException('Author must be an instance of User.');
        }

        $auditEvent->operationType = $logEventDTO->operationType;
        $auditEvent->entityNamespace = $logEventDTO->entity::class;
        $auditEvent->entityIdentifier = (string) $logEventDTO->entity->getAuditId();
        $auditEvent->requestData = $logEventDTO->additionalData;
        $auditEvent->eventData = $logEventDTO->eventData;

        return $auditEvent;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->setUpdatedAtValue();
    }

    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): void
    {
        $this->author = $author;
    }

    public function getEntityIdentifier(): string
    {
        return $this->entityIdentifier;
    }

    public function setEntityIdentifier(string $entityIdentifier): void
    {
        $this->entityIdentifier = $entityIdentifier;
    }

    public function getEntityNamespace(): string
    {
        return $this->entityNamespace;
    }

    public function setEntityNamespace(string $entityNamespace): void
    {
        $this->entityNamespace = $entityNamespace;
    }

    public function getOperationType(): AuditLogOperation
    {
        return $this->operationType;
    }

    public function setOperationType(AuditLogOperation $operationType): void
    {
        $this->operationType = $operationType;
    }

    public function getRequestData(): ?string
    {
        return $this->requestData;
    }

    public function setRequestData(?string $requestData): void
    {
        $this->requestData = $requestData;
    }

    public function getEventData(): string
    {
        return $this->eventData;
    }

    public function setEventData(string $eventData): void
    {
        $this->eventData = $eventData;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    public function getOrganization(): ?string
    {
        return $this->organization;
    }

    public function setOrganization(?string $organization): void
    {
        $this->organization = $organization;
    }
}
