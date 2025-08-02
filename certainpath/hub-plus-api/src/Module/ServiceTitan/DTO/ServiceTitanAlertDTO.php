<?php

declare(strict_types=1);

namespace App\Module\ServiceTitan\DTO;

use JsonSerializable;

class ServiceTitanAlertDTO implements JsonSerializable
{
    public const SEVERITY_INFO = 'info';
    public const SEVERITY_WARNING = 'warning';
    public const SEVERITY_ERROR = 'error';
    public const SEVERITY_SUCCESS = 'success';

    public const TYPE_CONNECTION = 'connection';
    public const TYPE_SYNC = 'sync';
    public const TYPE_TOKEN = 'token';
    public const TYPE_SYSTEM = 'system';

    public function __construct(
        private readonly string $id,
        private readonly string $type,
        private readonly string $severity,
        private readonly string $title,
        private readonly string $message,
        private readonly \DateTimeInterface $createdAt,
        private readonly ?array $metadata = null,
        private readonly ?string $actionUrl = null,
        private readonly ?string $actionLabel = null,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getSeverity(): string
    {
        return $this->severity;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getMetadata(): ?array
    {
        return $this->metadata;
    }

    public function getActionUrl(): ?string
    {
        return $this->actionUrl;
    }

    public function getActionLabel(): ?string
    {
        return $this->actionLabel;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'severity' => $this->severity,
            'title' => $this->title,
            'message' => $this->message,
            'createdAt' => $this->createdAt->format(\DateTimeInterface::ATOM),
            'metadata' => $this->metadata,
            'actionUrl' => $this->actionUrl,
            'actionLabel' => $this->actionLabel,
        ];
    }
}
