<?php

namespace App\Entity\Traits;

use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

trait PostProcessEntity
{
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    protected ?DateTimeInterface $processedAt = null;

    public function getProcessedAt(): ?DateTimeInterface
    {
        return $this->processedAt;
    }

    public function setProcessedAt(?DateTimeInterface $processedAt): static
    {
        $this->processedAt = $processedAt;

        return $this;
    }
}