<?php

namespace App\Entity;

use App\Entity\Trait\TimestampableDateTimeTZTrait;
use App\Entity\Trait\UuidTrait;
use App\Repository\FileDeleteJobRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FileDeleteJobRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\UniqueConstraint(fields: ['uuid'])]
class FileDeleteJob
{
    use TimestampableDateTimeTZTrait;
    use UuidTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $status = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2, nullable: true)]
    private ?string $progressPercent = null;

    #[ORM\Column(nullable: false, options: ['default' => 0])]
    private ?int $totalFiles = 0;

    #[ORM\Column(nullable: false, options: ['default' => 0])]
    private ?int $processedFiles = 0;

    #[ORM\Column(nullable: false, options: ['default' => 0])]
    private ?int $successfulDeletes = 0;

    #[ORM\Column(nullable: true)]
    private ?array $fileUuids = null;

    #[ORM\Column(nullable: true)]
    private ?array $failedItems = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getProgressPercent(): ?string
    {
        return $this->progressPercent;
    }

    public function setProgressPercent(?string $progressPercent): static
    {
        $this->progressPercent = $progressPercent;

        return $this;
    }

    public function getTotalFiles(): ?int
    {
        return $this->totalFiles;
    }

    public function setTotalFiles(int $totalFiles): static
    {
        $this->totalFiles = $totalFiles;

        return $this;
    }

    public function getProcessedFiles(): ?int
    {
        return $this->processedFiles;
    }

    public function setProcessedFiles(int $processedFiles): static
    {
        $this->processedFiles = $processedFiles;

        return $this;
    }

    public function getSuccessfulDeletes(): ?int
    {
        return $this->successfulDeletes;
    }

    public function setSuccessfulDeletes(int $successfulDeletes): static
    {
        $this->successfulDeletes = $successfulDeletes;

        return $this;
    }

    public function getFileUuids(): ?array
    {
        return $this->fileUuids;
    }

    public function setFileUuids(?array $fileUuids): static
    {
        $this->fileUuids = $fileUuids;

        return $this;
    }

    public function getFailedItems(): ?array
    {
        return $this->failedItems;
    }

    public function setFailedItems(?array $failedItems): static
    {
        $this->failedItems = $failedItems;

        return $this;
    }
}
