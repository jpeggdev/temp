<?php

namespace App\ValueObject;

readonly class NormalizedField
{
    public function __construct(
        private string $rawValue,
    ) {
    }

    public static function fromString(string $rawValue): self
    {
        return new self($rawValue);
    }

    public function getValue(): string
    {
        return strtolower(preg_replace('/\s+/', '_', trim($this->rawValue)));
    }
}
