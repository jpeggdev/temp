<?php

declare(strict_types=1);

namespace App\Entity\Trait;

trait UserTrait
{
    public function getAuditId(): ?int
    {
        return $this->id;
    }
}
