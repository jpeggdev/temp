<?php

namespace App\ValueObject;

readonly class UtcStamp
{
    private function __construct(
        private \DateTimeInterface $stamp,
    ) {
    }

    public static function create(\DateTimeInterface $stamp): self
    {
        return new self($stamp);
    }

    /**
     * @throws \DateMalformedStringException
     */
    public static function now(): self
    {
        return new self(new \DateTimeImmutable('now', new \DateTimeZone('UTC')));
    }

    public function asUtcString(): string
    {
        return $this->format('Y-m-d H:i:s+00:00');
    }

    public function format(string $format): string
    {
        return $this->asDateTimeImmutable()->format($format);
    }

    public function asDateTimeImmutable(): \DateTimeImmutable
    {
        return $this->stamp instanceof \DateTimeImmutable
            ? $this->stamp->setTimezone(new \DateTimeZone('UTC'))
            : \DateTimeImmutable::createFromInterface($this->stamp)->setTimezone(new \DateTimeZone('UTC'));
    }
}
