<?php

namespace App\ValueObject;

readonly class ByteSize
{
    private function __construct(
        private array $row,
    ) {
    }

    public static function fromArray(array $row): self
    {
        return new self($row);
    }

    public function getAdjustedValue(): float
    {
        return floor($this->getValue() / 6);
    }

    public function getValue(): int
    {
        return mb_strlen(serialize($this->row), '8bit');
    }
}
