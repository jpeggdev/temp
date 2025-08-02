<?php

namespace App\Entity\Trait;

use Doctrine\ORM\Mapping as ORM;

trait IsCertainPathTrait
{
    #[ORM\Column(nullable: false, options: ['default' => 'false'])]
    private ?bool $certainPath = false;

    public function isCertainPath(): ?bool
    {
        return $this->certainPath;
    }

    public function setCertainPath(bool $certainPath): static
    {
        $this->certainPath = $certainPath;

        return $this;
    }
}
