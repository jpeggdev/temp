<?php

declare(strict_types=1);

namespace App\Contract\Entity;

interface AuditableInterface
{
    public function getAuditId(): ?int;
}
