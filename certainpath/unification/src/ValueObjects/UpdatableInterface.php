<?php

namespace App\ValueObjects;

interface UpdatableInterface
{
    /**
     * Get auditId
     *
     * @return string|null
     */
    public function getPrimaryKeyColumn(): ?string;

    public function getUpdatableProperties(): array;

    public function toUpdateArray(): array;
}
