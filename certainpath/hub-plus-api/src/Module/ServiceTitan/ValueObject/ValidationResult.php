<?php

declare(strict_types=1);

namespace App\Module\ServiceTitan\ValueObject;

readonly class ValidationResult
{
    public function __construct(
        public bool $isValid,
        public array $errors = [],
    ) {
    }

    public static function valid(): self
    {
        return new self(true);
    }

    public static function invalid(array $errors): self
    {
        return new self(false, $errors);
    }

    public function hasErrors(): bool
    {
        return !$this->isValid;
    }
}
