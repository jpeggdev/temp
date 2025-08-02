<?php

namespace App\ValueObjects;

class MailPackageObject extends AbstractObject
{
    protected const KEY_FIELDS = [
        'name',
    ];

    public ?string $name = null;
    public ?string $series = null;
    public ?ProspectObject $prospect = null;
    public ?int $prospectId = null;

    public function getTableName(): string
    {
        return 'mail_package';
    }

    public function getTableSequence(): string
    {
        return 'mail_package_id_seq';
    }

    public function isValid(): bool
    {
        return (
            !empty($this->prospectId) &&
            !empty($this->name)
        );
    }

    public function toArray(): array
    {
        $createdAtFmt = $this->formatDate(
            $this->createdAt
        );

        $updatedAtFmt = $this->formatDate(
            $this->updatedAt
        );

        return [
            'id' => $this->_id,
            'series' => $this->series,
            'name' => $this->name,
            'created_at' => $createdAtFmt,
            'updated_at' => $updatedAtFmt,
        ];
    }

    public function populate(): static
    {
        $this->key = self::createKey([
            'name' => $this->name,
        ]);

        return $this;
    }
}
