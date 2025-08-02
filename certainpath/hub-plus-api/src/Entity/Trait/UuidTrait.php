<?php

declare(strict_types=1);

namespace App\Entity\Trait;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

trait UuidTrait
{
    #[ORM\Column(type: Types::GUID)]
    private ?string $uuid = null;

    #[ORM\PrePersist]
    public function generateUuidOnCreate(): void
    {
        $this->uuid = Uuid::uuid4()->toString();
    }

    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    public function setUuid(string $uuid): self
    {
        $this->uuid = $uuid;

        return $this;
    }
}
