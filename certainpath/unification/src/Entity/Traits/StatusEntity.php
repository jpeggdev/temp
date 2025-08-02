<?php

namespace App\Entity\Traits;

use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

trait StatusEntity
{
    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => true])]
    protected ?bool $isActive = true;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    protected ?bool $isDeleted = false;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?DateTimeInterface $deletedAt = null;

    public function isActive(): ?bool
    {
        return $this->isActive;
    }

    public function setActive(bool $isActive): static
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function isDeleted(): ?bool
    {
        return $this->isDeleted;
    }

    public function setDeleted(bool $isDeleted): static
    {
        if (
            $isDeleted === true
        ) {
            $this->setDeletedAt($this->getDeletedAt() ?? date_create_immutable());
        } else {
            $this->setDeletedAt(null);
        }

        return $this;
    }

    public function getDeletedAt(): ?DateTimeInterface
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(?DateTimeInterface $deletedAt): static
    {
        $this->deletedAt = $deletedAt;

        if ($this->deletedAt instanceof DateTimeInterface) {
            $this->isDeleted = true;
        } else {
            $this->isDeleted = false;
        }

        return $this;
    }
}