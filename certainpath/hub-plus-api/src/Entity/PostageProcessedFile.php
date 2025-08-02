<?php

namespace App\Entity;

use App\Module\Stochastic\Feature\PostageUploadsSftp\Repository\PostageProcessedFileRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PostageProcessedFileRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\UniqueConstraint(name: 'UNIQ_filename_hash', columns: ['filename', 'file_hash'])]
class PostageProcessedFile
{
    use Trait\TimestampableDateTimeTZTrait;
    use Trait\UuidTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $filename;

    #[ORM\Column(type: Types::STRING, length: 32, name: 'file_hash')]
    private string $fileHash;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, name: 'processed_at')]
    private \DateTimeImmutable $processedAt;

    #[ORM\Column(type: Types::INTEGER, name: 'records_processed', options: ['default' => 0])]
    private int $recordsProcessed = 0;

    #[ORM\Column(type: Types::STRING, length: 20, options: ['default' => 'SUCCESS'])]
    private string $status = 'SUCCESS';

    #[ORM\Column(type: Types::TEXT, name: 'error_message', nullable: true)]
    private ?string $errorMessage = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $metadata = null;

    public function __construct()
    {
        $this->processedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function setFilename(string $filename): static
    {
        $this->filename = $filename;
        return $this;
    }

    public function getFileHash(): string
    {
        return $this->fileHash;
    }

    public function setFileHash(string $fileHash): static
    {
        $this->fileHash = $fileHash;
        return $this;
    }

    public function getProcessedAt(): \DateTimeImmutable
    {
        return $this->processedAt;
    }

    public function setProcessedAt(\DateTimeImmutable $processedAt): static
    {
        $this->processedAt = $processedAt;
        return $this;
    }

    public function getRecordsProcessed(): int
    {
        return $this->recordsProcessed;
    }

    public function setRecordsProcessed(int $recordsProcessed): static
    {
        $this->recordsProcessed = $recordsProcessed;
        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        if (!in_array($status, ['SUCCESS', 'FAILED', 'PARTIAL'])) {
            throw new \InvalidArgumentException("Invalid status: $status. Must be SUCCESS, FAILED, or PARTIAL.");
        }
        $this->status = $status;
        return $this;
    }

    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    public function setErrorMessage(?string $errorMessage): static
    {
        $this->errorMessage = $errorMessage;
        return $this;
    }

    public function getMetadata(): ?array
    {
        return $this->metadata;
    }

    public function setMetadata(?array $metadata): static
    {
        $this->metadata = $metadata;
        return $this;
    }
}
