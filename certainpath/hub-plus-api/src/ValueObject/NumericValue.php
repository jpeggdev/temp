<?php

namespace App\ValueObject;

readonly class NumericValue
{
    private function __construct(
        private mixed $mixedValue,
    ) {
    }

    public static function fromMixedInput(null|string|float|int $mixedInput): self
    {
        return new self($mixedInput);
    }

    public function toSanitizedString(): string
    {
        if (!$this->mixedValue) {
            return '0.00';
        }
        if (is_numeric($this->mixedValue)) {
            return (string) $this->mixedValue;
        }
        return str_replace([',', '$', ' '], '', (string) $this->mixedValue);
    }
}
