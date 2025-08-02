<?php

namespace App\Entity;

use App\Repository\BulkBatchStatusEventRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BulkBatchStatusEventRepository::class)]
#[ORM\Index(name: "bulk_batch_status_event_year_week_idx", columns: ["year", "week"])]
#[ORM\UniqueConstraint(
    name: "bulk_batch_status_event_year_week_status_uniq",
    columns: ["year", "week", "batch_status_id"])]
class BulkBatchStatusEvent
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $year = null;

    #[ORM\Column]
    private ?int $week = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?BatchStatus $batchStatus = null;

    #[ORM\Column(type: 'json', options: ['default' => '[]'])]
    private array $updatedBatches = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getYear(): ?int
    {
        return $this->year;
    }

    public function setYear(int $year): static
    {
        $this->year = $year;

        return $this;
    }

    public function getWeek(): ?int
    {
        return $this->week;
    }

    public function setWeek(int $week): static
    {
        $this->week = $week;

        return $this;
    }

    public function getBatchStatus(): ?BatchStatus
    {
        return $this->batchStatus;
    }

    public function setBatchStatus(?BatchStatus $batchStatus): static
    {
        $this->batchStatus = $batchStatus;

        return $this;
    }

    public function getUpdatedBatches(): array
    {
        return $this->updatedBatches;
    }

    public function setUpdatedBatches(array $updatedBatches = []): static
    {
        $this->updatedBatches = $updatedBatches;

        return $this;
    }
}
