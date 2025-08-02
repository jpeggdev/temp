<?php

namespace App\Entity;

use App\Repository\CampaignIterationWeekRepository;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CampaignIterationWeekRepository::class)]
#[ORM\HasLifecycleCallbacks]
class CampaignIterationWeek
{
    use Traits\TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(type: "bigint")]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'campaignIterationWeeks')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?CampaignIteration $campaignIteration = null;

    #[ORM\Column]
    private ?int $weekNumber = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?DateTimeInterface $startDate = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?DateTimeInterface $endDate = null;

    #[ORM\Column(type: Types::BOOLEAN, nullable: false, options: ['default' => false])]
    private ?bool $isMailingDropWeek = false;

    #[ORM\OneToOne(targetEntity: Batch::class, mappedBy: 'campaignIterationWeek')]
    private ?Batch $batch = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCampaignIteration(): ?CampaignIteration
    {
        return $this->campaignIteration;
    }

    public function setCampaignIteration(?CampaignIteration $campaignIteration): static
    {
        $this->campaignIteration = $campaignIteration;

        return $this;
    }

    public function getWeekNumber(): ?int
    {
        return $this->weekNumber;
    }

    public function setWeekNumber(int $weekNumber): static
    {
        $this->weekNumber = $weekNumber;

        return $this;
    }

    public function getStartDate(): ?DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(DateTimeInterface $startDate): static
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(DateTimeInterface $endDate): static
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function isMailingDropWeek(): ?bool
    {
        return $this->isMailingDropWeek;
    }

    public function setIsMailingDropWeek(bool $isMailingDropWeek): static
    {
        $this->isMailingDropWeek = $isMailingDropWeek;

        return $this;
    }

    public function getBatch(): ?Batch
    {
        return $this->batch;
    }

    public function setBatch(?Batch $batch): static
    {
        $this->batch = $batch;

        if ($batch !== null) {
            $batch->setCampaignIterationWeek($this);
        }

        return $this;
    }
}
